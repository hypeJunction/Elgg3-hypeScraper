<?php

namespace hypeJunction\Scraper;

use Elgg\Hook;
use ElggMenuItem;

class CardMenu {

	/**
	 * Setup menu
	 *
	 * @param Hook $hook Hook
	 *
	 * @return ElggMenuItem[]
	 */
	public function __invoke(Hook $hook) {

		$menu = $hook->getValue();

		if (!elgg_is_admin_logged_in()) {
			return;
		}

		$href = $hook->getParam('href');
		if (!$href) {
			return;
		}

		$return[] = ElggMenuItem::factory([
			'name' => 'edit',
			'href' => elgg_http_add_url_query_elements('admin/scraper/edit', [
				'href' => $href,
			]),
			'text' => elgg_view_icon('pencil'),
			'title' => elgg_echo('edit'),
		]);

		$return[] = ElggMenuItem::factory([
			'name' => 'refetch',
			'href' => elgg_http_add_url_query_elements('action/admin/scraper/refetch', [
				'href' => $href,
			]),
			'text' => elgg_view_icon('refresh'),
			'title' => elgg_echo('scraper:refetch'),
			'is_action' => true,
			'confirm' => elgg_echo('scraper:refetch:confirm'),
		]);

		return $return;
	}
}
