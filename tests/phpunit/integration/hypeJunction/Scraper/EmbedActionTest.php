<?php

namespace hypeJunction\Scraper;

use Elgg\Exceptions\Http\BadRequestException;
use Elgg\IntegrationTestCase;
use Elgg\Request;

class EmbedActionTest extends IntegrationTestCase {

	public function getPluginID(): string {
		return 'hypescraper';
	}

	public function up(): void {}
	public function down(): void {}

	public function testThrowsBadRequestWhenEmbedViewProducesNoOutput(): void {
		$request = $this->getMockBuilder(Request::class)
			->disableOriginalConstructor()
			->getMock();
		$request->method('getParam')->willReturn('http://example.invalid/no-embed');

		$this->expectException(BadRequestException::class);
		(new EmbedAction())($request);
	}
}
