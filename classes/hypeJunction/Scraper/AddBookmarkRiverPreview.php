<?php

namespace hypeJunction\Scraper;

class AddBookmarkRiverPreview {

	/**
	 * Display a preview of a bookmark
	 *
	 * @param string $hook   'view_vars'
	 * @param string $type   "river/elements/layout"
	 * @param array  $return View vars
	 * @param array  $params Hook params
	 *
	 * @return array
	 */
	public function __invoke($hook, $type, $return, $params) {

		if (!elgg_get_plugin_setting('bookmarks', 'hypeScraper')) {
			return;
		}

		$item = elgg_extract('item', $return);
		if (!$item instanceof ElggRiverItem) {
			return;
		}

		if ($item->view != 'river/object/bookmarks/create') {
			return;
		}

		$object = $item->getObjectEntity();
		if (!elgg_instanceof($object, 'object', 'bookmarks')) {
			return;
		}

		$preview_type = elgg_get_plugin_setting('preview_type', 'hypeScraper', 'card');
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