<?php

elgg_signed_request_gatekeeper();

$url = get_input('url');

$data = hypeapps_scrape($url);
if (!$data) {
	$data = new \stdClass();
}

elgg_set_http_header('Content-Type: application/json');

echo json_encode($data);
