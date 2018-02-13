<?php

namespace hypeJunction\Scraper;

use Elgg\Hook;

class EmbedMenu {

	/**
	 * Setup embed menu
	 *
	 * @param Hook $hook Hook
	 * @return \ElggMenuItem[]
	 */
	public function __invoke(Hook $hook) {

		$menu = $hook->getValue();

		$menu[] = \ElggMenuItem::factory([
			'name' => 'player',
			'text' => elgg_echo('embed:player'),
			'priority' => 500,
			'data' => [
				'view' => 'embed/tab/player',
			],
		]);

		return $menu;
	}
}