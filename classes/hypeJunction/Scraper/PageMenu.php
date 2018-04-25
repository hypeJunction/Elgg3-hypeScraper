<?php

namespace hypeJunction\Scraper;

use Elgg\Hook;

class PageMenu {

	/**
	 * @elgg_plugin_hook register menu:page
	 *
	 * @param Hook $hook
	 *
	 * @return \Elgg\Menu\MenuItems|mixed|null
	 */
	public function __invoke(Hook $hook) {

		$menu = $hook->getValue();
		/* @var $menu \Elgg\Menu\MenuItems */

		if (!elgg_in_context('admin')) {
			return null;
		}

		// Admin
		$menu->add(\ElggMenuItem::factory([
			'name' => 'scraper',
			'href' => 'admin/scraper/preview',
			'text' => elgg_echo('admin:scraper:preview'),
			'context' => 'admin',
			'section' => 'develop'
		]));

		$menu->add(\ElggMenuItem::factory([
			'name' => 'scraper:cache',
			'href' => 'admin/scraper/cache',
			'text' => elgg_echo('admin:scraper:cache'),
			'context' => 'admin',
			'section' => 'develop'
		]));

		$menu->add(\ElggMenuItem::factory([
			'name' => 'scraper:hotfixes',
			'href' => 'admin/scraper/hotfixes',
			'text' => elgg_echo('admin:scraper:hotfixes'),
			'context' => 'admin',
			'section' => 'develop'
		]));

		return $menu;
	}
}