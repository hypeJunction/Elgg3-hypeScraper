<?php
/**
 * PHPUnit bootstrap for hypescraper (Elgg 4.x characterization suite).
 */

$elggRoot = '/var/www/html';

require_once $elggRoot . '/vendor/autoload.php';

$testClassesDir = $elggRoot . '/vendor/elgg/elgg/engine/tests/classes';
spl_autoload_register(function ($class) use ($testClassesDir) {
    $file = $testClassesDir . '/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

\Elgg\Application::getInstance()->bootCore();

if (function_exists('_elgg_services')) {
    _elgg_services()->plugins->generateEntities();
    $plugin = elgg_get_plugin_from_id('hypescraper');
    if ($plugin) {
        if (!$plugin->isEnabled()) {
            $plugin->enable();
        }
        if (!$plugin->isActive()) {
            try { $plugin->activate(); } catch (\Throwable $e) {}
        }
        try { elgg_trigger_event('init', 'system'); } catch (\Throwable $e) {}
    }
}
