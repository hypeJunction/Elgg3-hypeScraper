<?php

namespace hypeJunction\Scraper;

use Elgg\Hook;

class PrepareHtmlOutput {

	/**
	 * Prepare HTML output
	 *
	 * @param Hook $hook Hook
	 *
	 * @return array
	 */
	public function __invoke(Hook $hook) {

		if (!elgg_get_plugin_setting('linkify', 'hypeScraper')) {
			return null;
		}

		$value = $hook->getValue();

		$html = elgg_extract('html', $value);
		$options = elgg_extract('options', $value);

		if (elgg_extract('parse_hashtags', $options, true)) {
			$html = \hypeJunction\Scraper\Linkify::hashtags($html);
		}

		if (elgg_extract('parse_urls', $options, true)) {
			$html = \hypeJunction\Scraper\Linkify::urls($html);
		}

		if (elgg_extract('parse_usernames', $options, true)) {
			$html = \hypeJunction\Scraper\Linkify::usernames($html);
		}

		if (elgg_extract('parse_emails', $options, true)) {
			$html = \hypeJunction\Scraper\Linkify::emails($html);
		}

		$options['parse_hashtags'] = false;
		$options['parse_urls'] = false;
		$options['parse_usernames'] = false;
		$options['parse_emails'] = false;

		return [
			'html' => $html,
			'options' => $options,
		];
	}
}