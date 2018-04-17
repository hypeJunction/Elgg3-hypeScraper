<?php

namespace hypeJunction\Scraper;

/**
 * Representation of a URL
 */
class WebLocation {

	protected $url;

	/**
	 * Constructor
	 *
	 * @param string $url URL
	 */
	public function __construct($url) {
		$this->url = $url;
	}

	/**
	 * Get URL
	 * @return string
	 */
	public function getURL() {
		return $this->url;
	}

	/**
	 * Get URL data, including metatags
	 * @return WebResource
	 */
	public function getData() {
		$scraper = \hypeJunction\Scraper\ScraperService::instance();
		/* @var $scraper ScraperService */
		$data = $scraper->scrape($this->url) ? : [
			'url' => $this->url,
		];

		return new WebResource($data);
	}

}
