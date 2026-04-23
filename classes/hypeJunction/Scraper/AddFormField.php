<?php

namespace hypeJunction\Scraper;

use Elgg\Event;
use hypeJunction\Fields\Collection;

class AddFormField
{
    /**
     * @param Event $event
     * @return mixed
     */
    public function __invoke(Event $event)
    {

        $fields = $event->getValue();
        /* @var $field Collection */

        $fields->add('web_location', new WebLocationField([
            'type' => 'url',
            'priority' => 415,
        ]));

        return $fields;
    }
}
