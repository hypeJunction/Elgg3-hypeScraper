<?php

$href = get_input('href');

$svc = \hypeJunction\Scraper\ScraperService::instance();

$data = $svc->parse($href, true);
if ($data) {
	return elgg_ok_response($data, elgg_echo('scraper:refetch:success'));
}

return elgg_error_response(elgg_echo('scraper:refetch:error'));
