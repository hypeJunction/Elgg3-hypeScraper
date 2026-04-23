<?php

namespace hypeJunction\Scraper;

use Elgg\Event;

class ScrapeUrlMetadata {

	/**
     * @param Event $event
     * @return mixed
     */
    public function __invoke(Event $event) {
		$url = $event->getParam('url');

		return ScraperService::instance()->scrape($url);
	}
}