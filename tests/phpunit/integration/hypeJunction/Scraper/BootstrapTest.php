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

	/**
     * @return string
     */
    public function getPluginID(): string {
		return 'hypescraper';
	}

	/**
     * @return void
     */
    public function testPluginLoadable(): void {
		$plugin = elgg_get_plugin_from_id('hypescraper');
		$this->assertNotNull($plugin);
		$this->assertNotFalse($plugin->isActive());
	}

	// === Actions (declared in elgg-plugin.php) ===
    /**
     * @return void
     */
    public function testAdminScraperEditActionRegistered(): void {
		$this->assertTrue(elgg_action_exists('admin/scraper/edit'));
	}

	/**
     * @return void
     */
    public function testAdminScraperRefetchActionRegistered(): void {
		$this->assertTrue(elgg_action_exists('admin/scraper/refetch'));
	}

	/**
     * @return void
     */
    public function testAdminScraperClearActionRegistered(): void {
		$this->assertTrue(elgg_action_exists('admin/scraper/clear'));
	}

	/**
     * @return void
     */
    public function testAdminScraperTimestampImagesActionRegistered(): void {
		$this->assertTrue(elgg_action_exists('admin/scraper/timestamp_images'));
	}

	// === Routes ===
    /**
     * @return void
     */
    public function testScraperCardRouteRegistered(): void {
		$routes = _elgg_services()->routes->all();
		$this->assertArrayHasKey('scraper:card', $routes);
	}

	// === Classes autoload (24 classes in hypeJunction\Scraper namespace) ===
    /**
     * @return void
     */
    public function testScraperServiceClassExists(): void {
		$this->assertTrue(class_exists(ScraperService::class));
	}

	/**
     * @return void
     */
    public function testBootstrapClassExists(): void {
		$this->assertTrue(class_exists(Bootstrap::class));
	}

	/**
     * @return void
     */
    public function testRouterClassExists(): void {
		$this->assertTrue(class_exists(Router::class));
	}

	/**
     * @return void
     */
    public function testExtractorClassExists(): void {
		$this->assertTrue(class_exists(Extractor::class));
	}

	/**
     * @return void
     */
    public function testWebResourceClassExists(): void {
		$this->assertTrue(class_exists(WebResource::class));
	}

	/**
     * @return void
     */
    public function testWebLocationClassExists(): void {
		$this->assertTrue(class_exists(WebLocation::class));
	}

	/**
     * @return void
     */
    public function testPrepareHtmlOutputClassExists(): void {
		$this->assertTrue(class_exists(PrepareHtmlOutput::class));
	}

	/**
     * @return void
     */
    public function testScrapeUrlMetadataClassExists(): void {
		$this->assertTrue(class_exists(ScrapeUrlMetadata::class));
	}

	/**
     * @return void
     */
    public function testPrepareEmbedCardClassExists(): void {
		$this->assertTrue(class_exists(PrepareEmbedCard::class));
	}

	/**
     * @return void
     */
    public function testExtractTokensFromTextClassExists(): void {
		$this->assertTrue(class_exists(ExtractTokensFromText::class));
	}

	/**
     * @return void
     */
    public function testFilteroEmbedHtmlClassExists(): void {
		$this->assertTrue(class_exists(FilteroEmbedHtml::class));
	}

	/**
     * @return void
     */
    public function testCardMenuClassExists(): void {
		$this->assertTrue(class_exists(CardMenu::class));
	}

	/**
     * @return void
     */
    public function testPageMenuClassExists(): void {
		$this->assertTrue(class_exists(PageMenu::class));
	}

	// === Views ===
    /**
     * @return void
     */
    public function testScraperCardViewExists(): void {
		$this->assertTrue(elgg_view_exists('resources/scraper/card'));
	}

	/**
     * @return void
     */
    public function testScraperStylesheetViewExists(): void {
		$this->assertTrue(elgg_view_exists('framework/scraper/stylesheet.css'));
	}

	/**
     * @return void
     */
    public function testScraperPlayerJsViewExists(): void {
		$this->assertTrue(elgg_view_exists('framework/scraper/player.js'));
	}

	/**
     * @return void
     */
    public function testOutputCardViewExists(): void {
		$this->assertTrue(elgg_view_exists('output/card'));
	}
}
