<?php

/**
 * A tool for extracting, interpreting, caching and embedding remote resources.
 *
 * @author Ismayil Khayredinov <info@hypejunction.com>
 */
require_once __DIR__ . '/autoloader.php';

use hypeJunction\Scraper\AddFormField;
use hypeJunction\Scraper\Menus;
use hypeJunction\Scraper\Router;
use hypeJunction\Scraper\Views;

return function() {
	elgg_register_event_handler('init', 'system', function () {

		elgg_register_plugin_hook_handler('format:src', 'embed', [Views::class, 'viewCard']);
		elgg_register_plugin_hook_handler('extract:meta', 'all', [Views::class, 'getCard']);
		elgg_register_plugin_hook_handler('extract:qualifiers', 'all', [Views::class, 'extractTokens']);
		elgg_register_plugin_hook_handler('link:qualifiers', 'all', [Views::class, 'linkTokens']);
		elgg_register_plugin_hook_handler('view', 'output/longtext', [Views::class, 'linkifyLongtext']);

		elgg_register_plugin_hook_handler('fields', 'object', AddFormField::class);

		elgg_extend_view('elgg.css', 'framework/scraper/stylesheet.css');
		elgg_extend_view('admin.css', 'framework/scraper/stylesheet.css');
		elgg_extend_view('elgg.js', 'framework/scraper/player.js');

		// Bookmark previews
		if (elgg_is_active_plugin('bookmarks')) {
			elgg_register_plugin_hook_handler('view_vars', 'river/elements/layout', [
				Views::class,
				'addBookmarkRiverPreview'
			]);
			elgg_register_plugin_hook_handler('view_vars', 'object/elements/full', [
				Views::class,
				'addBookmarkProfilePreview'
			]);
		}

		// Basic XSS protection
		elgg_register_plugin_hook_handler('parse', 'framework:scraper', [Views::class, 'cleanEmbedHTML']);

		// Upgrades
		elgg_register_action('upgrade/scraper/move_to_db', __DIR__ . '/actions/upgrade/scraper/move_to_db.php', 'admin');

		// Cards
		elgg_register_plugin_hook_handler('register', 'menu:scraper:card', \hypeJunction\Scraper\CardMenu::class);

		// Admin
		elgg_register_menu_item('page', [
			'name' => 'scraper',
			'href' => 'admin/scraper/preview',
			'text' => elgg_echo('admin:scraper:preview'),
			'context' => 'admin',
			'section' => 'develop'
		]);

		elgg_register_menu_item('page', [
			'name' => 'scraper:cache',
			'href' => 'admin/scraper/cache',
			'text' => elgg_echo('admin:scraper:cache'),
			'context' => 'admin',
			'section' => 'develop'
		]);

		elgg_register_menu_item('page', [
			'name' => 'scraper:hotfixes',
			'href' => 'admin/scraper/hotfixes',
			'text' => elgg_echo('admin:scraper:hotfixes'),
			'context' => 'admin',
			'section' => 'develop'
		]);


		elgg_register_ajax_view('output/card');

		if (elgg()->has('shortcodes')) {
			elgg()->shortcodes->register('player');
			elgg_register_action('embed/player', \hypeJunction\Scraper\EmbedAction::class);
			elgg_register_plugin_hook_handler('register', 'menu:embed', \hypeJunction\Scraper\EmbedMenu::class);
			elgg_register_plugin_hook_handler('view_vars', 'river/elements/layout', \hypeJunction\Scraper\EmbedRiverAttachment::class, 999);
		}
	});
};
