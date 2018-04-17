<?php

namespace hypeJunction\Scraper;

use DatabaseException;
use Elgg\Application\Database;
use Elgg\Cache\CompositeCache;
use Elgg\Database\Delete;
use Elgg\Database\Insert;
use Elgg\Database\Select;
use Elgg\Di\ServiceFacade;
use ElggFile;
use hypeJunction\Parser;
use InvalidParameterException;
use IOException;

class ScraperService {

	use ServiceFacade;

	/**
	 * @var Parser
	 */
	protected $parser;

	/**
	 * @var CompositeCache
	 */
	protected $cache;

	/**
	 * @var Database
	 */
	protected $db;

	/**
	 * Constructor
	 *
	 * @param Parser         $parser Parser
	 * @param CompositeCache $cache  Cache
	 * @param Database       $db     Database
	 *
	 * @access private
	 */
	public function __construct(
		Parser $parser,
		CompositeCache $cache,
		Database $db
	) {
		$this->parser = $parser;
		$this->cache = $cache;
		$this->db = $db;
	}

	/**
	 * Scape a resource by URL
	 *
	 * @param string $url        URL to scrape
	 * @param bool   $cache_only Only return previously scraped data
	 * @param bool   $flush      Flush cache and re-parse
	 *
	 * @return array|false
	 */
	public function scrape($url, $cache_only = false, $flush = false) {
		if (!filter_var($url, FILTER_VALIDATE_URL)) {
			return false;
		}

		try {
			if ($cache_only) {
				return $this->get($url);
			}

			return $this->parse($url, $flush);
		} catch (\Exception $ex) {
			return false;
		}
	}

	/**
	 * Get scraped data
	 *
	 * @param string $url URL
	 *
	 * @return array|null
	 * @throws DatabaseException
	 *
	 * @access private
	 */
	public function get($url) {
		if (!$this->parser->isValidUrl($url)) {
			elgg_log(__METHOD__ . ' expects a valid URL: ' . $url);

			return null;
		}

		$data = $this->cache->load(sha1($url));
		if ($data) {
			return $data;
		}

		$qb = Select::fromTable('scraper_data');
		$qb->select('*')
			->where($qb->compare('url', '=', $url, ELGG_VALUE_STRING));

		$row = $this->db->getDataRow($qb);

		return $row ? unserialize($row->data) : null;
	}

	/**
	 * Find scraped resourced
	 *
	 * @param string $query Query to match against
	 *
	 * @return string[]
	 * @throws DatabaseException
	 * @access private
	 */
	public function find($query) {

		$qb = Select::fromTable('scraper_data');
		$qb->select('url')
			->where($qb->compare('url', 'like', $query, ELGG_VALUE_STRING));

		$rows = $this->db->getData($qb, function ($elem) {
			return $elem->url;
		});

		return $rows;
	}

	/**
	 * Parse and scrape a URL
	 *
	 * @param string $url     URL
	 * @param bool   $flush   Flush existing URL data
	 * @param bool   $recurse Recurse into subresources
	 *
	 * @return array|false
	 * @throws DatabaseException
	 * @throws IOException
	 * @throws InvalidParameterException
	 * @access private
	 */
	public function parse($url, $flush = false, $recurse = true) {

		elgg_log("Attempting to parse URL: $url");

		if (!$this->parser->isValidUrl($url)) {
			elgg_log("Invalid URL: $url");

			return false;
		}

		if ($flush) {
			$this->delete($url);
		} else {
			$data = $this->get($url);
			if (isset($data)) {
				return $data;
			}
		}

		try {
			$response = $this->parser->request($url);
		} catch (\Exception $ex) {
			elgg_log($ex->getMessage(), 'ERROR');
			$data = false;
		}

		if (!$response instanceof \GuzzleHttp\Psr7\Response || $response->getStatusCode() != 200) {
			$this->save($url, false);

			return false;
		}

		$post_max_size = elgg_get_ini_setting_in_bytes('post_max_size');
		$upload_max_filesize = elgg_get_ini_setting_in_bytes('upload_max_filesize');
		$max_upload = $upload_max_filesize > $post_max_size ? $post_max_size : $upload_max_filesize;

		$content_length = $response->getHeader('Content-Length');
		if (is_array($content_length)) {
			$content_length = array_shift($content_length);
		}

		if ((int) $content_length > $max_upload) {
			// Large images eat up memory
			$this->save($url, false);

			return false;
		}

		try {
			$data = $this->parser->parse($url);
		} catch (\Exception $ex) {
			// There is an issue with the DOM markup and we are unable to
			// scrape the data. Giving up.
			elgg_log($ex->getMessage(), 'ERROR');
			$data = false;
		}

		if (!$data) {
			$this->save($url, false);

			return false;
		}

		$type = elgg_extract('type', $data);

		switch ($type) {
			case 'photo' :
			case 'image' :
				$image = $this->saveImageFromUrl($url);
				if ($image instanceof ElggFile) {
					$data['width'] = $image->natural_width;
					$data['height'] = $image->natural_height;
					$data['filename'] = $image->getFilename();
					$data['owner_guid'] = $image->owner_guid;
					$data['thumbnail_url'] = elgg_get_inline_url($image);
				}
				break;

			default :
				if ($recurse) {
					$data = $this->parseThumbs($data);
				}
				break;
		}

		$data = elgg_trigger_plugin_hook('parse', 'framework:scraper', [
			'url' => $url,
		], $data);

		elgg_log("URL data parsed: " . print_r($data, true));

		$this->save($url, $data);

		return $data;
	}

	/**
	 * Save URL data to the database
	 *
	 * @param string $url  URL
	 * @param array  $data Data
	 *
	 * @return bool
	 * @access private
	 * @throws DatabaseException
	 */
	public function save($url, $data = false) {
		if (!$url) {
			return false;
		}

		$qb = Insert::intoTable('scraper_data');
		$qb->setValue('url', $qb->param($url, ELGG_VALUE_STRING))
			->setValue('hash', $qb->param(sha1($url), ELGG_VALUE_STRING))
			->setValue('data', $qb->param(serialize($data)));

		$result = $this->db->insertData($qb);

		if ($result) {
			$this->cache->save(sha1($url), $data);

			return true;
		}

		return false;
	}

	/**
	 * Delete URL data from DB and cache
	 *
	 * @param string $url URL
	 *
	 * @return bool
	 * @throws DatabaseException
	 * @access private
	 */
	public function delete($url) {

		$parse = $this->parse($url);

		if (!empty($parse['assets'])) {
			foreach ($parse['assets'] as $asset) {
				if (!empty($asset['filename'])) {
					$file = new ElggFile();
					$file->owner_guid = elgg_get_site_entity()->guid;
					$file->setFilename($asset['filename']);
					$file->delete();
				}
			}
		}

		$this->cache->delete(sha1($url));

		$qb = Delete::fromTable('scraper_data');
		$qb->where($qb->compare('url', '=', $url, ELGG_VALUE_STRING));

		$result = $this->db->deleteData($qb);

		return (bool) $result;
	}

	/**
	 * Saves an image on Elgg's filestore
	 *
	 * @param string $url URL of the image
	 *
	 * @return ElggFile|false
	 * @throws IOException
	 * @throws InvalidParameterException
	 * @access private
	 */
	public function saveImageFromUrl($url) {

		$mime = $this->parser->getContentType($url);
		switch ($mime) {
			case 'image/jpeg' :
			case 'image/jpg' :
				$ext = 'jpg';
				break;
			case 'image/gif' :
				$ext = 'gif';
				break;
			case 'image/png' :
				$ext = 'png';
				break;
			default :
				return false;
		}

		$basename = sha1($url);
		$this->parser;
		$raw_bytes = $this->parser->read($url);
		if (empty($raw_bytes)) {
			return;
		}

		$site = elgg_get_site_entity();
		$tmp = new \ElggFile();
		$tmp->owner_guid = $site->guid;
		$tmp->setFilename("scraper_cache/tmp/$basename.$ext");
		$tmp->open('write');
		$tmp->write($raw_bytes);
		$tmp->close();
		unset($raw_bytes);

		//@Todo - looks like we need some way to check this in core
		// instead of elgg_save_resized_image() OOMing
		if (!$this->hasMemoryToResize($tmp->getFilenameOnFilestore())) {
			$tmp->delete();

			return false;
		}

		$lower_threashold = elgg_get_plugin_setting('cache_thumb_size_lower_threshold', 'hypeScraper', 100);
		$upper_threshold = elgg_get_plugin_setting('cache_thumb_size_upper_threshold', 'hypeScraper', 1500);
		$imagesize = getimagesize($tmp->getFilenameOnFilestore());
		if (!$imagesize || $imagesize[0] < $lower_threashold || $imagesize[0] > $upper_threshold) {
			$tmp->delete();

			return false;
		}

		$image = new \ElggFile();
		$image->owner_guid = $site->guid;
		$image->setFilename("scraper_cache/thumbs/$basename.jpg");

		$image->natural_width = $imagesize[0];
		$image->natural_height = $imagesize[1];

		$image->open('write');
		$image->close();

		$size = elgg_get_plugin_setting('cache_thumb_size', 'hypeScraper', 500);
		$thumb = elgg_save_resized_image($tmp->getFilenameOnFilestore(), $image->getFilenameOnFilestore(), [
			'w' => $size,
			'h' => $size,
			'upscale' => false,
			'square' => false,
		]);

		unset($thumb);
		$tmp->delete();

		return $image;
	}

	/**
	 * Parse thumbnails from scraped data
	 *
	 * @param array $data Data
	 *
	 * @return array
	 * @throws DatabaseException
	 * @throws IOException
	 * @throws InvalidParameterException
	 * @access private
	 */
	public function parseThumbs(array $data = []) {
		$assets = [];
		$thumbnails = (array) elgg_extract('thumbnails', $data, []);
		$icons = (array) elgg_extract('icons', $data, []);

		// Try 3 images and choose the one with highest dimensions
		$thumbnails = array_filter(array_unique(array_merge($thumbnails, $icons)));
		$thumbs_parsed = 0;
		foreach ($thumbnails as $thumbnail) {
			$thumbnail = elgg_normalize_url($thumbnail);
			$asset = $this->parse($thumbnail, false, false);

			if ($asset) {
				$thumbs_parsed++;
				$assets[] = $asset;
			}

			if ($thumbs_parsed == 5) {
				break;
			}
		}

		$data['assets'] = array_values(array_filter($assets));
		usort($data['assets'], function ($a, $b) {
			if ($a['width'] == $b['width'] && $a['height'] == $b['height']) {
				return 0;
			}

			return ($a['width'] > $b['width'] || $a['height'] > $b['height']) ? -1 : 1;
		});

		if (isset($data['assets'][0]['thumbnail_url'])) {
			$data['thumbnail_url'] = $data['assets'][0]['thumbnail_url'];
		}

		return $data;
	}

	/**
	 * Do we estimate that we have enough memory available to resize an image?
	 *
	 * @param string $source - the source path of the file
	 *
	 * @return bool
	 * @access private
	 */
	public function hasMemoryToResize($source) {
		$imginfo = getimagesize($source);
		$requiredMemory1 = ceil($imginfo[0] * $imginfo[1] * 5.35);
		$requiredMemory2 = ceil($imginfo[0] * $imginfo[1] * ($imginfo['bits'] / 8) * $imginfo['channels'] * 2.5);
		$requiredMemory = (int) max($requiredMemory1, $requiredMemory2);

		$mem_avail = elgg_get_ini_setting_in_bytes('memory_limit');
		$mem_used = memory_get_usage();

		$mem_avail = $mem_avail - $mem_used - 20971520; // 20 MB buffer, yeah arbitrary but necessary

		return $mem_avail > $requiredMemory;
	}

	/**
	 * Returns registered service name
	 * @return string
	 */
	public static function name() {
		return 'scraper';
	}
}
