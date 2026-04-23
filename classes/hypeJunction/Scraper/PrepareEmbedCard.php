<?php

namespace hypeJunction\Scraper;

use Elgg\Event;

class PrepareEmbedCard {

	public function __invoke(Event $event) {
		$href = $event->getParam('src');

		$preview_type = elgg_get_plugin_setting('preview_type', 'hypescraper', 'card');

		if ($preview_type != 'card') {
			return elgg_view('output/player', [
				'href' => $href,
				'fallback' => true,
			]);
		} else {
			return elgg_view('output/card', [
				'href' => $href,
			]);
		}
	}
}