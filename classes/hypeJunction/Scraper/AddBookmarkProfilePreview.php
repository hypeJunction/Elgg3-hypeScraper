<?php

namespace hypeJunction\Scraper;

use Elgg\Event;

class AddBookmarkProfilePreview
{
    /**
     * @param Event $event
     * @return mixed
     */
    public function __invoke(Event $event)
    {

        if (!elgg_get_plugin_setting('bookmarks', 'hypescraper')) {
            return;
        }

        $return = $event->getValue();

        $entity = elgg_extract('entity', $return);
        if (!$entity instanceof \ElggObject || $entity->getSubtype() !== 'bookmarks') {
            return;
        }

        $return['body'] .= elgg_view('output/player', [
            'href' => $entity->address,
        ]);

        return $return;
    }
}
