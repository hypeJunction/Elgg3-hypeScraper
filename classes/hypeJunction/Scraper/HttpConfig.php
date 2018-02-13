<?php

namespace hypeJunction\Scraper;

use Elgg\PluginHooksService;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Cookie\SetCookie;

class HttpConfig {

	protected $hooks;

	/**
	 * Constructor
	 *
	 * @param PluginHooksService $hooks Hook service
	 */
	public function __construct(PluginHooksService $hooks) {
		$this->hooks = $hooks;
	}

	/**
	 * Returns default config for http requests
	 * @return array
	 */
	public function getHttpClientConfig() {
		$jar = new CookieJar();
		$jar->setCookie(new SetCookie([
			'Name' => 'Elgg',
			'Value' => elgg_get_session()->getId(),
			'Domain' => parse_url(elgg_get_site_url(), PHP_URL_HOST),
		]));

		$config = [
			'headers' => [
				'User-Agent' => implode(' ', [
					'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.2.12)',
					'Gecko/20101026',
					'Firefox/3.6.12'
				]),
			],
			'allow_redirects' => [
				'max' => 10,
				'strict' => true,
				'referer' => true,
				'protocols' => ['http', 'https']
			],
			'timeout' => 5,
			'connect_timeout' => 5,
			'verify' => false,
			'cookies' => $jar,
		];

		return $this->hooks->trigger('http:config', 'framework:scraper', null, $config);
	}

}
