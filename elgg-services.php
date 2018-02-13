<?php

return [
	'scraper.http_config' => \DI\object(\hypeJunction\Scraper\HttpConfig::class)
		->constructor(\DI\get('hooks')),

	'scraper.http_client' => \DI\object(\GuzzleHttp\Client::class)
		->constructor(\DI\factory([
			\hypeJunction\Scraper\HttpConfig::class,
			'getHttpClientConfig'
		])),

	'scraper.parser' => \DI\object(\hypeJunction\Parser::class)
		->constructor(\DI\get('scraper.http_client')),

	'scraper.cache' => \DI\object(\Elgg\Cache\CompositeCache::class)
		->constructor(
			'scraper',
			\DI\get('config'),
			ELGG_CACHE_PERSISTENT | ELGG_CACHE_FILESYSTEM
		),

	'scraper' => \DI\object(\hypeJunction\Scraper\ScraperService::class)
		->constructor(
			\DI\get('scraper.parser'),
			\DI\get('scraper.cache'),
			\DI\get('db')
		),

	'posts.web_location' => \DI\object(\hypeJunction\Scraper\Post::class)
];
