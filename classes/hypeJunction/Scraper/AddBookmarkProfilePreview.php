<?php

namespace hypeJunction\Scraper;

use Elgg\Hook;

class AddBookmarkProfilePreview {

	public function __invoke(Hook $hook) {

		if (!elgg_get_plugin_setting('bookmarks', 'hypescraper')) {
			return;
		}

		$return = $hook->getValue();

		$entity = elgg_extract('entity', $return);
		if (!$entity instanceof \ElggObject || $entity->getSubtype() !== 'bookmarks') {
			return;
		}

		$return['body'] .= elgg_view('output/player', [
			'href' => $entity->address,
		]);

		return $return;
	}
}