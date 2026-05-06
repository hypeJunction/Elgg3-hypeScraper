<?php

namespace hypeJunction\Scraper;

use ElggEntity;
use hypeJunction\Fields\Field;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * WebLocationField class.
 */
class WebLocationField extends Field {

	/**
	 * isVisible.
	 *
	 * @param ElggEntity $entity  entity
	 * @param mixed      $context context
	 *
	 * @return mixed
	 */
	public function isVisible(ElggEntity $entity, $context = null) {
		$params = [
			'entity' => $entity,
		];

		$enabled = elgg()->hooks->trigger(
			'uses:web_location',
			"$entity->type:$entity->subtype",
			$params,
			false
		);

		if (!$enabled) {
			return false;
		}

		return parent::isVisible($entity, $context);
	}

	/**
	 * save.
	 *
	 * @param ElggEntity   $entity     entity
	 * @param ParameterBag $parameters parameters
	 *
	 * @return mixed
	 */
	public function save(ElggEntity $entity, ParameterBag $parameters) {
		$value = $parameters->get($this->name);

		$svc = elgg()->{'posts.web_location'};

		/* @var $svc \hypeJunction\Scraper\Post */

		return $svc->setWebLocation($entity, $value);
	}

	/**
	 * retrieve.
	 *
	 * @param ElggEntity $entity entity
	 *
	 * @return mixed
	 */
	public function retrieve(ElggEntity $entity) {
		$svc = elgg()->{'posts.web_location'};
		/* @var $svc \hypeJunction\Scraper\Post */

		$location = $svc->getWebLocation($entity);
		if ($location) {
			return $location->getURL();
		}

		return null;
	}
}
