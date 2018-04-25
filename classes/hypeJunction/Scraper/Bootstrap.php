<?php

namespace hypeJunction\Scraper;

use Elgg\Includer;
use Elgg\PluginBootstrap;

class Bootstrap extends PluginBootstrap {

	/**
	 * Get plugin root
	 * @return string
	 */
	protected function getRoot() {
		return dirname(dirname(dirname(dirname(__FILE__))));
	}

	/**
	 * {@inheritdoc}
	 */
	public function load() {
		Includer::requireFileOnce($this->getRoot() . '/autoloader.php');
	}

	/**
	 * {@inheritdoc}
	 */
	public function boot() {

	}

	/**
	 * {@inheritdoc}
	 */
	public function init() {

		elgg_extend_view('elgg.css', 'framework/scraper/stylesheet.css');
		elgg_extend_view('admin.css', 'framework/scraper/stylesheet.css');
		elgg_extend_view('elgg.js', 'framework/scraper/player.js');

		elgg_register_ajax_view('output/card');

		elgg_register_plugin_hook_handler('format:src', 'embed', PrepareEmbedCard::class);
		elgg_register_plugin_hook_handler('extract:meta', 'all', ScrapeUrlMetadata::class);
		elgg_register_plugin_hook_handler('extract:qualifiers', 'all', ExtractTokensFromText::class);
		elgg_register_plugin_hook_handler('prepare', 'html', PrepareHtmlOutput::class, 100);

		elgg_register_plugin_hook_handler('fields', 'object', AddFormField::class);

		// Bookmark previews
		if (elgg_is_active_plugin('bookmarks')) {
			elgg_register_plugin_hook_handler('view_vars', 'river/elements/layout', AddBookmarkRiverPreview::class);
			elgg_register_plugin_hook_handler('view_vars', 'object/elements/full', AddBookmarkProfilePreview::class);
		}

		// Basic XSS protection
		elgg_register_plugin_hook_handler('parse', 'framework:scraper', FilteroEmbedHtml::class);

		// Menus
		elgg_register_plugin_hook_handler('register', 'menu:scraper:card', CardMenu::class);
		elgg_register_plugin_hook_handler('register', 'menu:page', PageMenu::class);

		if (elgg()->has('shortcodes')) {
			elgg()->shortcodes->register('player');

			elgg_register_action('embed/player', \hypeJunction\Scraper\EmbedAction::class);

			elgg_register_plugin_hook_handler('register', 'menu:embed', EmbedMenu::class);
			elgg_register_plugin_hook_handler('view_vars', 'river/elements/layout', EmbedRiverAttachment::class, 999);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function ready() {

	}

	/**
	 * {@inheritdoc}
	 */
	public function shutdown() {

	}

	/**
	 * {@inheritdoc}
	 */
	public function activate() {

	}

	/**
	 * {@inheritdoc}
	 */
	public function deactivate() {

	}

	/**
	 * {@inheritdoc}
	 */
	public function upgrade() {

	}
}