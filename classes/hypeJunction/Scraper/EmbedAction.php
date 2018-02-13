<?php

namespace hypeJunction\Scraper;

use Elgg\BadRequestException;
use Elgg\Request;

class EmbedAction {

	/**
	 * Get safe embed code
	 *
	 * @param Request $request Request
	 *
	 * @return \Elgg\Http\OkResponse
	 * @throws BadRequestException
	 */
	public function __invoke(Request $request) {
		$url = $request->getParam('url');

		$output = elgg_view('embed/safe/player', [
			'url' => $url,
		]);

		if (empty($output)) {
			throw new BadRequestException();
		}

		return elgg_ok_response($output);
	}
}