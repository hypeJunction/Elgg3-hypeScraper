<?php

namespace hypeJunction\Scraper;

use Elgg\Event;

class EmbedMenu {

	/**
     * @param Event $event
     * @return mixed
     */
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