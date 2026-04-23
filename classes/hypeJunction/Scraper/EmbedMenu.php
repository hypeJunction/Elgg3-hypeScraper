<?php

namespace hypeJunction\Scraper;

use Elgg\Event;

class EmbedMenu {

	public function __invoke(Event $event) {

		$menu = $event->getValue();

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