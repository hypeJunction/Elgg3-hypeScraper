<?php

if (!elgg()->has('shortcodes')) {
	return;
}

$svc = elgg()->shortcodes;
/* @var $svc \hypeJunction\Shortcodes\ShortcodesService */

$scraper = \hypeJunction\Scraper\ScraperService::instance();
/* @var $scraper \hypeJunction\Scraper\ScraperService */

$url = elgg_extract('url', $vars);

$attrs = [
	'url' => $url,
];

$tag = $svc->generate('player', $attrs);

$output = elgg_format_element('div', [
	'contenteditable' => 'false',
], $tag);

echo elgg_trigger_plugin_hook('prepare:player', 'embed', $vars, $output);
