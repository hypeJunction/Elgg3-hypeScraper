<?php

namespace hypeJunction\Scraper;

use Elgg\IntegrationTestCase;

/**
 * Characterization suite for hypescraper on Elgg 4.x.
 *
 * hypescraper has no entity subtypes — it's a URL/metadata extraction
 * service — so the test surface is plugin lifecycle, class autoloading,
 * admin action registration characterization, Bootstrap::init hook wiring,
 * and the conditional shortcode/ajax wiring that depends on whether
 * sibling plugins (bookmarks, shortcodes) are active.
 */
class BootstrapTest extends IntegrationTestCase {

	public function getPluginID(): string {
		return 'hypescraper';
	}

	public function up() {}
	public function down() {}

	// --- plugin lifecycle ---

	public function testPluginIsRegistered() {
		$this->assertInstanceOf(\ElggPlugin::class, elgg_get_plugin_from_id('hypescraper'));
	}

	public function testPluginIsEnabled() {
		$this->assertTrue(elgg_get_plugin_from_id('hypescraper')->isEnabled());
	}

	public function testPluginIsActive() {
		$this->assertTrue(elgg_get_plugin_from_id('hypescraper')->isActive());
	}

	// --- class autoloading (subset of 24 classes — the ones Bootstrap::init
	//     actually touches, plus the main service objects) ---

	public function testBootstrapClassLoads() {
		$this->assertTrue(class_exists(Bootstrap::class));
	}

	public function testWebLocationClassLoads() {
		$this->assertTrue(class_exists(WebLocation::class));
	}

	public function testWebResourceClassLoads() {
		$this->assertTrue(class_exists(WebResource::class));
	}

	public function testPostUtilityClassLoads() {
		$this->assertTrue(class_exists(Post::class));
	}

	public function testScraperServiceClassLoads() {
		$this->assertTrue(class_exists(ScraperService::class));
	}

	public function testLinkifyClassLoads() {
		$this->assertTrue(class_exists(Linkify::class));
	}

	public function testExtractorClassLoads() {
		$this->assertTrue(class_exists(Extractor::class));
	}

	public function testScrapeUrlMetadataClassLoads() {
		$this->assertTrue(class_exists(ScrapeUrlMetadata::class));
	}

	public function testPrepareEmbedCardClassLoads() {
		$this->assertTrue(class_exists(PrepareEmbedCard::class));
	}

	public function testPrepareHtmlOutputClassLoads() {
		$this->assertTrue(class_exists(PrepareHtmlOutput::class));
	}

	public function testFilteroEmbedHtmlClassLoads() {
		$this->assertTrue(class_exists(FilteroEmbedHtml::class));
	}

	public function testRouterClassLoads() {
		$this->assertTrue(class_exists(Router::class));
	}

	// --- WebLocation value-object shape ---

	public function testWebLocationStoresUrl() {
		$loc = new WebLocation('https://example.test/page?x=1');
		$this->assertSame('https://example.test/page?x=1', $loc->getURL());
	}

	// --- Post utility wraps WebLocation from entity metadata ---

	public function testPostGetWebLocationReturnsNullWhenEntityHasNone() {
		$user = elgg_call(ELGG_IGNORE_ACCESS, fn() => $this->createUser());
		$post = new Post();
		$this->assertNull($post->getWebLocation($user));
	}

	public function testPostGetWebLocationReturnsWebLocationWhenSet() {
		$user = elgg_call(ELGG_IGNORE_ACCESS, fn() => $this->createUser());
		elgg_call(ELGG_IGNORE_ACCESS, function () use ($user) {
			$user->web_location = 'https://example.test/user';
			$user->save();
		});
		$post = new Post();
		$loc = $post->getWebLocation($user);
		$this->assertInstanceOf(WebLocation::class, $loc);
		$this->assertSame('https://example.test/user', $loc->getURL());
	}

	// --- action registry (all 4 declarative admin actions) ---

	public function testAdminScraperActionsRegistered() {
		// Characterization: all 4 admin scraper actions are in the
		// registry even in a stateless context. These use the legacy
		// file-path form (access='admin' with no 'controller' key) but
		// the 'admin/*' path prefix makes Elgg register them via its
		// built-in admin action discovery — different from hypeinbox's
		// 'hypeInbox/settings/save' which silently fails lookup because
		// the camelCase plugin-id prefix bypasses the admin discovery
		// path. Pin this distinction so both behaviors are documented.
		$svc = _elgg_services()->actions;
		$this->assertTrue($svc->exists('admin/scraper/edit'));
		$this->assertTrue($svc->exists('admin/scraper/refetch'));
		$this->assertTrue($svc->exists('admin/scraper/clear'));
		$this->assertTrue($svc->exists('admin/scraper/timestamp_images'));
	}

	// --- Bootstrap::init hook wiring ---

	public function testFormatSrcEmbedHookWired() {
		$handlers = _elgg_services()->hooks->getAllHandlers();
		$this->assertArrayHasKey('format:src', $handlers);
		$this->assertArrayHasKey('embed', $handlers['format:src']);
	}

	public function testExtractMetaAllHookWired() {
		$handlers = _elgg_services()->hooks->getAllHandlers();
		$this->assertArrayHasKey('extract:meta', $handlers);
		$this->assertArrayHasKey('all', $handlers['extract:meta']);
	}

	public function testExtractQualifiersAllHookWired() {
		$handlers = _elgg_services()->hooks->getAllHandlers();
		$this->assertArrayHasKey('extract:qualifiers', $handlers);
		$this->assertArrayHasKey('all', $handlers['extract:qualifiers']);
	}

	public function testPrepareHtmlHookWired() {
		$handlers = _elgg_services()->hooks->getAllHandlers();
		$this->assertArrayHasKey('prepare', $handlers);
		$this->assertArrayHasKey('html', $handlers['prepare']);
	}

	public function testFieldsObjectHookWired() {
		$handlers = _elgg_services()->hooks->getAllHandlers();
		$this->assertArrayHasKey('fields', $handlers);
		$this->assertArrayHasKey('object', $handlers['fields']);
	}

	public function testFrameworkScraperParseHookWired() {
		$handlers = _elgg_services()->hooks->getAllHandlers();
		$this->assertArrayHasKey('parse', $handlers);
		$this->assertArrayHasKey('framework:scraper', $handlers['parse']);
	}

	public function testScraperCardMenuHookWired() {
		$handlers = _elgg_services()->hooks->getAllHandlers();
		$this->assertArrayHasKey('register', $handlers);
		$this->assertArrayHasKey('menu:scraper:card', $handlers['register']);
	}

	public function testPageMenuHookWired() {
		$handlers = _elgg_services()->hooks->getAllHandlers();
		$this->assertArrayHasKey('register', $handlers);
		$this->assertArrayHasKey('menu:page', $handlers['register']);
	}

	// --- conditional wiring (bookmarks + shortcodes) ---

	public function testBookmarksPreviewHookNotWiredWhenBookmarksInactive() {
		// hypescraper wires view_vars:river/elements/layout only when
		// the bookmarks plugin is active. In our isolated container,
		// bookmarks is NOT mounted so the conditional branch doesn't
		// fire — pin that bookmarks ISN'T considered active here.
		$this->assertFalse(elgg_is_active_plugin('bookmarks'));
	}

	public function testShortcodesEmbedActionNotRegisteredWhenShortcodesAbsent() {
		// elgg()->has('shortcodes') is false in the default Elgg 4.x
		// service container — hypescraper's embed/player action only
		// registers when the shortcode service is available, so it
		// should NOT be in the registry here.
		$this->assertFalse(_elgg_services()->actions->exists('embed/player'));
	}
}
