<?php

echo elgg_view_form('admin/scraper/preview', [
	'action' => elgg_get_current_url(),
	'method' => 'GET',
	'disable_security' => true,
]);

$card = '';
$href = get_input('href');
if ($url) {
	$card = elgg_view('output/card', [
		'href' => $href,
	]);
}

echo elgg_format_element('div', [
	'id' => 'scraper-preview',
], $card);

elgg_import_esm('admin/scraper/preview');
