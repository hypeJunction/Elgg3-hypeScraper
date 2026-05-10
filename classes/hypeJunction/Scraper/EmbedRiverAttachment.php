<?php

namespace hypeJunction\Scraper;

use Elgg\Event;

class EmbedRiverAttachment
{
    /**
     * @param Event $event
     * @return mixed
     */
    public function __invoke(Event $event)
    {

        $vars = $event->getValue();

        if (isset($vars['attachments'])) {
            return null;
        }

        $item = elgg_extract('item', $vars);
        if (!$item instanceof \ElggRiverItem) {
            return null;
        }

        $object = $item->getObjectEntity();
        if (!$object instanceof \ElggObject) {
            return null;
        }

        $description = $object->description;
        if (!$description) {
            return null;
        }

        $svc = elgg()->shortcodes;
        /* @var $svc \hypeJunction\Shortcodes\ShortcodesService */

        $matches = $svc->extract($description);

        if (!empty($matches['player'][0])) {
            $vars['attachments'] = elgg_format_element('div', [
                'class' => 'embed-player-listing-preview elgg-river-attachment',
            ], elgg_view('shortcodes/player', $matches['player'][0]));
        }

        return $vars;
    }
}
