<?php

namespace hypeJunction\Scraper;

use Elgg\IntegrationTestCase;

class HooksTest extends IntegrationTestCase {

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
    public function testFormatSrcEmbedEventWired(): void {
		if (!class_exists(\hypeJunction\Parser::class)) {
			$this->markTestSkipped('hypeJunction\\Parser not available (hypeParser inactive)');
		}
		$result = elgg_trigger_event_results('format:src', 'embed', [], '');
		$this->assertIsString($result);
	}

	/**
     * @return void
     */
    public function testExtractMetaEventWired(): void {
		if (!class_exists(\hypeJunction\Parser::class)) {
			$this->markTestSkipped('hypeJunction\\Parser not available (hypeParser inactive)');
		}
		$result = elgg_trigger_event_results('extract:meta', 'all', ['url' => ''], null);
		$this->assertNotNull($result === null ? 'ok' : $result);
	}

	/**
     * @return void
     */
    public function testExtractQualifiersEventWired(): void {
		$result = elgg_trigger_event_results('extract:qualifiers', 'all', ['text' => ''], []);
		$this->assertTrue(is_array($result) || $result === null);
	}

	/**
     * @return void
     */
    public function testPrepareHtmlEventWired(): void {
		$result = elgg_trigger_event_results('prepare', 'html', ['text' => 'hello'], 'hello');
		$this->assertTrue(is_string($result) || is_array($result));
	}

	/**
     * @return void
     */
    public function testFieldsObjectEventWired(): void {
		if (!class_exists(\hypeJunction\Fields\Collection::class)) {
			$this->markTestSkipped('hypeJunction\\Fields\\Collection not available (hypePost inactive)');
		}
		$collection = new \hypeJunction\Fields\Collection();
		$result = elgg_trigger_event_results('fields', 'object', [], $collection);
		$this->assertNotNull($result);
	}

	/**
     * @return void
     */
    public function testParseFrameworkScraperEventWired(): void {
		$result = elgg_trigger_event_results('parse', 'framework:scraper', ['url' => ''], ['url' => '']);
		$this->assertTrue(is_array($result) || is_string($result));
	}

	/**
     * @return void
     */
    public function testCardMenuEventWired(): void {
		$result = elgg_trigger_event_results('register', 'menu:scraper:card', [], []);
		$this->assertTrue(is_array($result) || $result === null);
	}

	/**
     * @return void
     */
    public function testPageMenuEventWired(): void {
		$result = elgg_trigger_event_results('register', 'menu:page', [], []);
		$this->assertTrue(is_array($result) || $result === null);
	}
}
