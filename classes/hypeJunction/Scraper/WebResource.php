<?php

namespace hypeJunction\Scraper;

/**
 * @property string   $title
 * @property string   $description
 * @property array    $tags
 * @property string   $url
 * @property string   $canonical
 * @property string[] $icons
 * @property array    $metatags
 * @property string   $provider_name
 * @property string   $resource_type
 * @property array    $thumbnails
 * @property string   $thumbnail_url
 * @property string   $html
 */
class WebResource extends \ArrayObject {

	/**
	 * {@inheritdoc}
	 */
	public function __construct($input = [], int $flags = self::ARRAY_AS_PROPS, string $iterator_class = "ArrayIterator") {
		parent::__construct($input, $flags, $iterator_class);
	}

	/**
	 * Returns meta tag value
	 *
	 * @param string $name Tag name
	 *
	 * @return mixed
	 */
	public function meta($name) {
		return elgg_extract($name, $this->metatags);
	}
}
