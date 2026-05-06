<?php
/**
 *
 */

namespace hypeJunction\Scraper;

/**
 * FilteroEmbedHtml class.
 */
class FilteroEmbedHtml {

	/**
	 * Filter parsed metatags
	 *
	 * @param \Elgg\Hook $hook Hook
	 *
	 * @return array|void
	 */
	public function __invoke(\Elgg\Hook $hook) {

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

		// only allow html from whitelisted domains
		if (empty($matches)) {
			unset($return['html']);
		} else if (!preg_match('/<iframe|video|audio/i', $return['html'])) {
			// only allow iframe, video, and audio tags
			unset($return['html']);
		}

		return $return;
	}
}
