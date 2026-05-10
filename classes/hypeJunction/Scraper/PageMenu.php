<?php

namespace hypeJunction\Scraper;

use Elgg\Event;

class PageMenu
{
    /**
     * @param Event $event
     * @return mixed
     */
    public function __invoke(Event $event)
    {

        $menu = $event->getValue();
        /* @var $menu \Elgg\Menu\MenuItems */

        if (!elgg_in_context('admin')) {
            return null;
        }

        // Admin
        $menu->add(\ElggMenuItem::factory([
            'name' => 'scraper',
            'href' => 'admin/scraper/preview',
            'text' => elgg_echo('admin:scraper:preview'),
            'context' => 'admin',
            'section' => 'develop'
        ]));

        $menu->add(\ElggMenuItem::factory([
            'name' => 'scraper:cache',
            'href' => 'admin/scraper/cache',
            'text' => elgg_echo('admin:scraper:cache'),
            'context' => 'admin',
            'section' => 'develop'
        ]));

        $menu->add(\ElggMenuItem::factory([
            'name' => 'scraper:hotfixes',
            'href' => 'admin/scraper/hotfixes',
            'text' => elgg_echo('admin:scraper:hotfixes'),
            'context' => 'admin',
            'section' => 'develop'
        ]));

        return $menu;
    }
}
