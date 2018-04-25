<?php

return [
	'bootstrap' => \hypeJunction\Scraper\Bootstrap::class,

	'actions' => [
		'admin/scraper/edit' => [
			'access' => 'admin',
		],
		'admin/scraper/refetch' => [
			'access' => 'admin',
		],
		'admin/scraper/clear' => [
			'access' => 'admin',
		],
		'admin/scraper/timestamp_images' => [
			'access' => 'admin',
		],
	],

	'routes' => [
		'scraper:card' => [
			'path' => '/scraper',
			'resource' => 'scraper/card',
			'defaults' => [
				'viewtype' => 'default',
			],
		],
	],

	'settings' => [
		'linkify' => true,
		'bookmarks' => true,
		'preview_type' => 'card',
	],
];
