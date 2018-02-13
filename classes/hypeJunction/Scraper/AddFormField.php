<?php

namespace hypeJunction\Scraper;

use Elgg\Hook;
use ElggEntity;

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

		$fields['web_location'] = [
			'#type' => 'url',
			'#setter' => function (ElggEntity $entity, $value) use ($hook) {
				$svc = $hook->elgg()->{'posts.web_location'};

				/* @var $svc \hypeJunction\Scraper\Post */

				return $svc->setWebLocation($entity, $value);
			},
			'#getter' => function (ElggEntity $entity) use ($hook) {
				$svc = $hook->elgg()->{'posts.web_location'};
				/* @var $svc \hypeJunction\Scraper\Post */

				$location = $svc->getWebLocation($entity);
				if ($location) {
					return $location->getURL();
				}

				return null;
			},
			'#priority' => 415,
			'#visibility' => function (ElggEntity $entity) use ($hook) {
				$params = [
					'entity' => $entity,
				];

				return $hook->elgg()->hooks->trigger(
					'uses:web_location',
					"$entity->type:$entity->subtype",
					$params,
					false
				);
			}
		];

		return $fields;
	}
}
