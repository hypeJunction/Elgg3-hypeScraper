<?php

$base_dir = dirname(dirname(dirname(dirname(__FILE__))));

require_once $base_dir . '/engine/settings.php';
require_once $base_dir . '/vendor/autoload.php';
require_once dirname(dirname(__FILE__)) . '/classes/hypeJunction/Scraper/Services/ThumbServer.php';

global $CONFIG;
$conf = new \Elgg\Database\Config($CONFIG);

$server = new \hypeJunction\Scraper\Services\ThumbServer($conf, $CONFIG->dbprefix);
$server->serve();