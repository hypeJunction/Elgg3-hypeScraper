<?php

return [
	'scraper.http_config' => \DI\create(\hypeJunction\Scraper\HttpConfig::class)
		->constructor(\DI\get('hooks')),

	'scraper.http_client' => \DI\create(\GuzzleHttp\Client::class)
		->constructor(\DI\factory([
			\hypeJunction\Scraper\HttpConfig::class,
			'getHttpClientConfig'
		])),

	'scraper.parser' => \DI\create(\hypeJunction\Parser::class)
		->constructor(\DI\get('scraper.http_client')),

	'scraper.cache' => \DI\create(\Elgg\Cache\CompositeCache::class)
		->constructor(
			'scraper',
			\DI\get('config'),
			ELGG_CACHE_PERSISTENT | ELGG_CACHE_FILESYSTEM
		),

	'scraper' => \DI\create(\hypeJunction\Scraper\ScraperService::class)
		->constructor(
			\DI\get('scraper.parser'),
			\DI\get('scraper.cache'),
			\DI\get('db')
		),

	'posts.web_location' => \DI\create(\hypeJunction\Scraper\Post::class)
];
