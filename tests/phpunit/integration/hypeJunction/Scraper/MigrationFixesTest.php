<?php

namespace hypeJunction\Scraper;

use Elgg\EventsService;
use Elgg\IntegrationTestCase;
use Elgg\Upgrade\AsynchronousUpgrade;
use ElggMenuItem;
use hypeJunction\Scraper\Upgrade\MigrateScraperDataToJson;

/**
 * Regression + behavior lock-in for the Elgg 7.x migration of hypeScraper.
 *
 * One test per non-trivial migration fix (see CHANGELOG / commit refs in the
 * gap spec) plus the pure Extractor/Linkify token logic. Each asserts the
 * FIXED behavior so a regression that re-introduces the pre-7.x code fails.
 */
class MigrationFixesTest extends IntegrationTestCase {

	public function up() {}
	public function down() {}

	/**
	 * @return string
	 */
	public function getPluginID(): string {
		return 'hypescraper';
	}

	/**
	 * b82f547 — client JS migrated to ESM: .mjs views loaded via elgg_import_esm,
	 * orphaned scraper/play.js deleted.
	 *
	 * @return void
	 */
	public function testEsmPlayerViewsMigratedToMjs(): void {
		$this->assertTrue(
			elgg_view_exists('framework/scraper/player.mjs'),
			'framework/scraper/player.mjs ESM view must exist'
		);
		$this->assertTrue(
			elgg_view_exists('embed/tab/player.mjs'),
			'embed/tab/player.mjs ESM view must exist'
		);
		$this->assertFalse(
			elgg_view_exists('scraper/play.js'),
			'orphaned pre-migration scraper/play.js must be gone'
		);
	}

	/**
	 * 6b2f4eb — global helpers must load (require_once lib/functions.php from
	 * elgg-plugin.php); hypeapps_extract_tokens delegates to Extractor::all.
	 *
	 * @return void
	 */
	public function testGlobalHelpersLoadedAndDelegate(): void {
		$this->assertTrue(function_exists('hypeapps_scrape'));
		$this->assertTrue(function_exists('hypeapps_extract_tokens'));
		$this->assertTrue(function_exists('hypeapps_linkify_tokens'));

		$tokens = hypeapps_extract_tokens('follow #elgg');
		$this->assertArrayHasKey('hashtags', $tokens);
		$this->assertContains('#elgg', $tokens['hashtags']);
	}

	/**
	 * df3da91 — depend on hypefields for the hypeJunction\Fields namespace.
	 *
	 * @return void
	 */
	public function testPluginDeclaresHypefieldsDependency(): void {
		$plugin = elgg_get_plugin_from_id('hypescraper');
		$manifest = include rtrim($plugin->getPath(), '/\\') . '/elgg-plugin.php';

		$this->assertSame(
			true,
			$manifest['plugin']['dependencies']['hypefields']['must_be_active'] ?? null,
			'elgg-plugin.php must declare hypefields as a required dependency'
		);
	}

	/**
	 * 5c34c9e / 7564b32 — Upgrade\Batch became abstract in 6.x; the data-to-JSON
	 * migration now extends AsynchronousUpgrade with run(Result,$offset):Result.
	 *
	 * @return void
	 */
	public function testMigrateBatchIsAsynchronousUpgrade(): void {
		$batch = new MigrateScraperDataToJson();

		$this->assertInstanceOf(AsynchronousUpgrade::class, $batch);
		$this->assertSame(2024010101, $batch->getVersion());
		$this->assertSame('hypescraper_migrate_data_to_json', MigrateScraperDataToJson::IDENTIFIER);
		$this->assertFalse($batch->needsIncrementOffset());

		$return = (new \ReflectionMethod(MigrateScraperDataToJson::class, 'run'))->getReturnType();
		$this->assertNotNull($return);
		$this->assertSame(\Elgg\Upgrade\Result::class, $return->getName());
	}

	/**
	 * c53dbff — HttpConfig ctor takes EventsService (was PluginHooksService) and
	 * uses triggerResults() in getHttpClientConfig().
	 *
	 * @return void
	 */
	public function testHttpConfigConstructorTakesEventsService(): void {
		$param = (new \ReflectionMethod(HttpConfig::class, '__construct'))->getParameters()[0];
		$type = $param->getType();

		$this->assertNotNull($type);
		$this->assertSame(EventsService::class, $type->getName());
	}

	/**
	 * 5cde845 — Router::serveScraperPages requires a valid HMAC 'm' token for
	 * anonymous requests and returns false without it (isValidViewtype replaces
	 * the removed elgg_is_registered_viewtype only past the token gate).
	 *
	 * @return void
	 */
	public function testRouterRejectsAnonymousWithoutHmacToken(): void {
		if (elgg_is_logged_in()) {
			$this->markTestSkipped('Router HMAC guard only applies to anonymous requests');
		}

		$this->assertFalse(
			Router::serveScraperPages([]),
			'anonymous request without an HMAC token must be rejected'
		);
	}

	/**
	 * 5005b56 — get_user_by_username() removed; Linkify::callbackUsername uses
	 * elgg_get_user_by_username() and returns the original match for an unknown
	 * username (no anchor injected).
	 *
	 * @return void
	 */
	public function testLinkifyUsernameUnknownReturnsOriginalText(): void {
		$out = Linkify::usernames('ping @zzz_nobody_qwerty for info');

		$this->assertStringContainsString('@zzz_nobody_qwerty', $out);
		$this->assertStringNotContainsString('scraper-username', $out);
	}

	/**
	 * a148fdc — page/iframe.php uses elgg_get_current_language() for 7.x.
	 *
	 * @return void
	 */
	public function testIframeViewUsesElggGetCurrentLanguage(): void {
		$plugin = elgg_get_plugin_from_id('hypescraper');
		$src = file_get_contents(rtrim($plugin->getPath(), '/\\') . '/views/default/page/iframe.php');

		$this->assertStringContainsString('elgg_get_current_language()', $src);
		$this->assertDoesNotMatchRegularExpression('/(?<![_a-z])get_current_language\s*\(/', $src);
	}

	/**
	 * a698e5f — 7.x menu register event value is an array. PageMenu appends its
	 * three develop-section items and returns the array (not $menu->add()).
	 *
	 * @return void
	 */
	public function testPageMenuAppendsAdminItemsAndReturnsArray(): void {
		elgg_push_context('admin');
		try {
			$result = elgg_trigger_event_results('register', 'menu:page', [], []);
		} finally {
			elgg_pop_context();
		}

		$this->assertIsArray($result);

		$names = [];
		foreach ($result as $item) {
			if ($item instanceof ElggMenuItem) {
				$names[] = $item->getName();
			}
		}

		$this->assertContains('scraper', $names);
		$this->assertContains('scraper:cache', $names);
		$this->assertContains('scraper:hotfixes', $names);
	}

	/**
	 * Pure Extractor token logic: html_entity_decode + keyed, de-duplicated
	 * arrays; hashtags exclude hex codes living inside HTML tags.
	 *
	 * @return void
	 */
	public function testExtractorAllReturnsKeyedDedupedTokens(): void {
		$tokens = Extractor::all('Check https://elgg.org email hi@elgg.org #elgg #elgg @admin');

		$this->assertSame(['urls', 'hashtags', 'emails', 'usernames'], array_keys($tokens));
		$this->assertContains('https://elgg.org', $tokens['urls']);
		$this->assertContains('hi@elgg.org', $tokens['emails']);
		$this->assertContains('@admin', $tokens['usernames']);
		// two "#elgg" collapse to a single de-duplicated entry
		$this->assertSame(['#elgg'], array_values($tokens['hashtags']));

		// hex color inside an HTML tag is NOT treated as a hashtag
		$hashes = Extractor::hashtags('<span style="color:#abcdef">hi</span> #real');
		$this->assertContains('#real', $hashes);
		$this->assertNotContains('#abcdef', $hashes);
	}

	/**
	 * Pure Linkify logic: bare hashtag is wrapped in a scraper-hashtag anchor,
	 * while a URL already inside an <a>...</a> is left untouched (no double wrap).
	 *
	 * @return void
	 */
	public function testLinkifyWrapsHashtagButSkipsAnchoredUrl(): void {
		$hashtag = Linkify::hashtags('hello #elgg world');
		$this->assertStringContainsString('scraper-hashtag', $hashtag);
		$this->assertStringContainsString('#elgg</a>', $hashtag);

		$anchored = Linkify::urls('<a href="http://x.io/">http://x.io/</a>');
		$this->assertStringNotContainsString('scraper-url', $anchored);
		$this->assertSame(1, substr_count($anchored, '<a '));
	}
}
