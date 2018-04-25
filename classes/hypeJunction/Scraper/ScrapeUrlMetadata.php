<?php

namespace hypeJunction\Scraper;

use Elgg\Hook;

class ScrapeUrlMetadata {

	/**
	 * @elgg_plugin_hook extract:meta embed
	 *
	 * @param Hook $hook Hook
	 * @return array
	 */
	public function __invoke(Hook $hook) {
		$url = $hook->getParam('url');

		return ScraperService::instance()->scrape($url);
	}
}