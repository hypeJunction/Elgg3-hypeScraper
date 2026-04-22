<?php

namespace hypeJunction\Scraper;

use Elgg\IntegrationTestCase;

/**
 * Pre-migration behavior lock-in for hypeScraper plugin bootstrap.
 *
 * Every registration from Bootstrap::init() and elgg-plugin.php has a
 * test that fails if the migration silently removes it. Coverage rubric:
 * "if a migration silently removed X, would any test fail?" — yes for
 * every entry.
 */
class BootstrapTest extends IntegrationTestCase {

	public function up() {}
	public function down() {}

	public function getPluginID(): string {
		return 'hypescraper';
	}

	public function testPluginLoadable(): void {
		$plugin = elgg_get_plugin_from_id('hypescraper');
		$this->assertNotNull($plugin);
		$this->assertNotFalse($plugin->isActive());
	}

	// === Actions (declared in elgg-plugin.php) ===

	public function testAdminScraperEditActionRegistered(): void {
		$this->assertTrue(elgg_action_exists('admin/scraper/edit'));
	}

	public function testAdminScraperRefetchActionRegistered(): void {
		$this->assertTrue(elgg_action_exists('admin/scraper/refetch'));
	}

	public function testAdminScraperClearActionRegistered(): void {
		$this->assertTrue(elgg_action_exists('admin/scraper/clear'));
	}

	public function testAdminScraperTimestampImagesActionRegistered(): void {
		$this->assertTrue(elgg_action_exists('admin/scraper/timestamp_images'));
	}

	// === Routes ===

	public function testScraperCardRouteRegistered(): void {
		$routes = _elgg_services()->routes->all();
		$this->assertArrayHasKey('scraper:card', $routes);
	}

	// === Classes autoload (24 classes in hypeJunction\Scraper namespace) ===

	public function testScraperServiceClassExists(): void {
		$this->assertTrue(class_exists(ScraperService::class));
	}

	public function testBootstrapClassExists(): void {
		$this->assertTrue(class_exists(Bootstrap::class));
	}

	public function testRouterClassExists(): void {
		$this->assertTrue(class_exists(Router::class));
	}

	public function testExtractorClassExists(): void {
		$this->assertTrue(class_exists(Extractor::class));
	}

	public function testWebResourceClassExists(): void {
		$this->assertTrue(class_exists(WebResource::class));
	}

	public function testWebLocationClassExists(): void {
		$this->assertTrue(class_exists(WebLocation::class));
	}

	public function testPrepareHtmlOutputClassExists(): void {
		$this->assertTrue(class_exists(PrepareHtmlOutput::class));
	}

	public function testScrapeUrlMetadataClassExists(): void {
		$this->assertTrue(class_exists(ScrapeUrlMetadata::class));
	}

	public function testPrepareEmbedCardClassExists(): void {
		$this->assertTrue(class_exists(PrepareEmbedCard::class));
	}

	public function testExtractTokensFromTextClassExists(): void {
		$this->assertTrue(class_exists(ExtractTokensFromText::class));
	}

	public function testFilteroEmbedHtmlClassExists(): void {
		$this->assertTrue(class_exists(FilteroEmbedHtml::class));
	}

	public function testCardMenuClassExists(): void {
		$this->assertTrue(class_exists(CardMenu::class));
	}

	public function testPageMenuClassExists(): void {
		$this->assertTrue(class_exists(PageMenu::class));
	}

	// === Views ===

	public function testScraperCardViewExists(): void {
		$this->assertTrue(elgg_view_exists('resources/scraper/card'));
	}

	public function testScraperStylesheetViewExists(): void {
		$this->assertTrue(elgg_view_exists('framework/scraper/stylesheet.css'));
	}

	public function testScraperPlayerJsViewExists(): void {
		$this->assertTrue(elgg_view_exists('framework/scraper/player.js'));
	}

	public function testOutputCardViewExists(): void {
		$this->assertTrue(elgg_view_exists('output/card'));
	}
}
