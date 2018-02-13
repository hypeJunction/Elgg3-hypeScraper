<?php

namespace hypeJunction\Scraper;

use ElggEntity;

class Post {

	/**
	 * Set entity location in the WWW
	 *
	 * @param ElggEntity $entity Entity
	 * @param string     $url    URL
	 *
	 * @return bool
	 */
	public function setWebLocation(ElggEntity $entity, $url) {
		$entity->setVolatileData('web_location', $this->getWebLocation($entity));

		$entity->web_location = $url;

		return elgg_trigger_event('update', 'object:web_location', $entity);
	}

	/**
	 * Returns web location
	 *
	 * @param ElggEntity $entity Entity
	 *
	 * @return WebLocation|null
	 */
	public function getWebLocation(ElggEntity $entity) {
		if ($entity->web_location) {
			return new WebLocation($entity->web_location);
		}

		return null;
	}
}
