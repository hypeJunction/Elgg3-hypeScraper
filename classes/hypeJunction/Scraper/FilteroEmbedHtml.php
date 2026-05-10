<?php

namespace hypeJunction\Scraper;

use Elgg\Hook;

class FilteroEmbedHtml {

	public function __invoke(Hook $hook) {

		$return = $hook->getValue();

		if (empty($return['html'])) {
			return;
		}

		$url = parse_url(elgg_extract('url', $return, ''), PHP_URL_HOST);
		$canonical_url = parse_url(elgg_extract('canonical', $return, ''), PHP_URL_HOST);

		$domains = ScraperService::instance()->getoEmbedDomains();

		$matches = array_map(function ($elem) use ($url, $canonical_url) {
			$elem = preg_quote($elem);
			$domain_pattern = "/(.+?)?$elem/i";

			return preg_match($domain_pattern, $url) || preg_match($domain_pattern, $canonical_url);
		}, $domains);

		$matches = array_filter($matches);

		if (empty($matches)) {
			unset($return['html']);
		} elseif (!preg_match('/<iframe|video|audio/i', $return['html'])) {
			unset($return['html']);
		}

		return $return;
	}
}