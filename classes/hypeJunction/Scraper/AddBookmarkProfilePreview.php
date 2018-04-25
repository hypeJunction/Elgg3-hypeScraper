<?php

namespace hypeJunction\Scraper;

class AddBookmarkProfilePreview {

	/**
	 * Display a preview of a bookmark
	 *
	 * @param string $hook   'view_vars'
	 * @param string $type   "object/elements/full"
	 * @param array  $return View vars
	 * @param array  $params Hook params
	 *
	 * @return array
	 */
	public function __invoke($hook, $type, $return, $params) {

		if (!elgg_get_plugin_setting('bookmarks', 'hypeScraper')) {
			return;
		}

		$entity = elgg_extract('entity', $return);
		if (!elgg_instanceof($entity, 'object', 'bookmarks')) {
			return;
		}

		$return['body'] .= elgg_view('output/player', [
			'href' => $entity->address,
		]);

		return $return;
	}
}