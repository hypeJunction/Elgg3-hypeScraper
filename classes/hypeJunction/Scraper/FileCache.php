<?php

namespace hypeJunction\Scraper;

use Elgg\Cache\Pool;
use Flintstone\Flintstone;

/**
 * @access private
 */
class FileCache implements Pool {

	/**
	 * @var Flintstone
	 */
	private $cache;

	/**
	 * __construct.
	 *
	 * @return mixed
	 */
	public function __construct() {
		$this->cache = new Flintstone('scraper_cache', [
			'dir' => elgg_get_config('dataroot'),
		]);
	}

	/**
	 * get.
	 *
	 * @param mixed    $key      key
	 * @param callable $callback callback
	 * @param mixed    $default  default
	 *
	 * @return mixed
	 */
	public function get($key, callable $callback = null, $default = null) {
		$value = $this->cache->get($key);
		if (!isset($value)) {
			$value = $default;
		}

		if (is_callable($callback)) {
			return call_user_func($callback, $value);
		}

		return $value;
	}

	/**
	 * invalidate.
	 *
	 * @param mixed $key key
	 *
	 * @return mixed
	 */
	public function invalidate($key) {
		$this->cache->delete($key);
	}

	/**
	 * put.
	 *
	 * @param mixed $key   key
	 * @param mixed $value value
	 *
	 * @return mixed
	 */
	public function put($key, $value) {
		$this->cache->set($key, $value);
	}
}
