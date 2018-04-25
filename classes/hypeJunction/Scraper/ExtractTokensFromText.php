<?php

namespace hypeJunction\Scraper;

use Elgg\Hook;

class ExtractTokensFromText {

	/**
	 * Extract qualifiers such as hashtags, usernames, urls, and emails
	 *
	 * @elgg_plugin_hook extract:qualifiers scraper
	 *
	 * @param Hook $hook Hook
	 *
	 * @return array
	 */
	public static function extractTokens(Hook $hook) {
		$text = $hook->getParam('source');

		return Extractor::all($text);
	}
}