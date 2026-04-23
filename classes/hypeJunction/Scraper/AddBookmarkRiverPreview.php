<?php

namespace hypeJunction\Scraper;

use Elgg\Event;

class AddBookmarkRiverPreview {

	public function __invoke(Event $event) {

		if (!elgg_get_plugin_setting('bookmarks', 'hypescraper')) {
			return;
		}

		$return = $event->getValue();

		$item = elgg_extract('item', $return);
		if (!$item instanceof \ElggRiverItem) {
			return;
		}

		if ($item->view != 'river/object/bookmarks/create') {
			return;
		}

		$object = $item->getObjectEntity();
		if (!$object instanceof \ElggObject || $object->getSubtype() !== 'bookmarks') {
			return;
		}

		$preview_type = elgg_get_plugin_setting('preview_type', 'hypescraper', 'card');
		if ($preview_type != 'card') {
			$return['attachments'] = elgg_view('output/player', [
				'href' => $object->address,
				'fallback' => true,
			]);
		} else {
			$return['attachments'] = elgg_view('output/card', [
				'href' => $object->address,
			]);
		}

		return $return;
	}
}
