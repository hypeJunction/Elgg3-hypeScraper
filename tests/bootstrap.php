<?php
/**
 * PHPUnit bootstrap for hypeScraper pre-migration tests.
 *
 * Activates the plugin and runs its Bootstrap lifecycle so hooks/actions/
 * view extensions registered in init() are in place for tests.
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
    $boot_plugin = elgg_get_plugin_from_id('hypeScraper') ?: elgg_get_plugin_from_id('hypescraper');
    if ($boot_plugin) {
        if (!$boot_plugin->isEnabled()) {
            $boot_plugin->enable();
        }
        if (!$boot_plugin->isActive()) {
            try { $boot_plugin->activate(); } catch (\Throwable $e) {}
        }
        try { $boot_plugin->init(); } catch (\Throwable $e) {}
    }
}
