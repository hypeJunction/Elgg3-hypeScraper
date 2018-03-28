<?php

namespace hypeJunction\Scraper;

use Elgg\Hook;
use hypeJunction\Fields\Collection;

class AddFormField {

	/**
	 * Add slug field
	 *
	 * @param Hook $hook Hook
	 *
	 * @return mixed
	 */
	public function __invoke(Hook $hook) {

		$fields = $hook->getValue();
		/* @var $field Collection */

		$fields->add('web_location', new WebLocationField([
			'type' => 'url',
			'priority' => 415,
		]));

		return $fields;
	}
}
