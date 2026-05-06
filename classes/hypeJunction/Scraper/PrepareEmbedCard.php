<?php

namespace hypeJunction\Scraper;

use Elgg\Hook;

/**
 * PrepareEmbedCard class.
 */
class PrepareEmbedCard {

	/**
	 * @elgg_plugin_hook format:src all
	 *
	 * @param Hook $hook Hook
	 *
	 * @return string
	 */
	public function __invoke(Hook $hook) {
		$href = $hook->getParam('src');

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
