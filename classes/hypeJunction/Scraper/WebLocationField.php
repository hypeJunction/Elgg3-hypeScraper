<?php

namespace hypeJunction\Scraper;

use ElggEntity;
use hypeJunction\Fields\Field;
use Symfony\Component\HttpFoundation\ParameterBag;

class WebLocationField extends Field
{
    /**
     * @param ElggEntity $entity
     * @param mixed $context
     * @return mixed
     */
    public function isVisible(ElggEntity $entity, $context = null)
    {
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
     * @param ElggEntity $entity
     * @param ParameterBag $parameters
     * @return mixed
     */
    public function save(ElggEntity $entity, ParameterBag $parameters)
    {
        $value = $parameters->get($this->name);

        $svc = elgg()->{'posts.web_location'};

        /* @var $svc \hypeJunction\Scraper\Post */

        return $svc->setWebLocation($entity, $value);
    }

    /**
     * @param ElggEntity $entity
     * @return mixed
     */
    public function retrieve(ElggEntity $entity)
    {
        $svc = elgg()->{'posts.web_location'};
        /* @var $svc \hypeJunction\Scraper\Post */

        $location = $svc->getWebLocation($entity);
        if ($location) {
            return $location->getURL();
        }

        return null;
    }
}
