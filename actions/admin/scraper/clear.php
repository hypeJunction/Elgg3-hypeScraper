<?php

$domain = get_input('domain');
if (!$domain) {
	return elgg_ok_response();
}

$svc = \hypeJunction\Scraper\ScraperService::instance();
$urls = $svc->find($domain);

foreach ($urls as $url) {
	$svc->delete($url);
}
