<?php

namespace hypeJunction\Scraper\Upgrade;

use Elgg\Upgrade\AsynchronousUpgrade;
use Elgg\Upgrade\Result;

/**
 * Re-encodes scraper_data rows from PHP serialize() to JSON (5.x migration).
 */
class MigrateScraperDataToJson extends AsynchronousUpgrade
{
    const IDENTIFIER = 'hypescraper_migrate_data_to_json';
    const VERSION = 2024010101;

    public function getVersion(): int
    {
        return self::VERSION;
    }

    public function needsIncrementOffset(): bool
    {
        return false;
    }

    public function shouldBeSkipped(): bool
    {
        return false;
    }

    public function countItems(): int
    {
        $prefix = $this->getPrefix();
        $row = elgg()->db->getConnection('read')->executeQuery(
            "SELECT COUNT(*) AS cnt FROM {$prefix}scraper_data WHERE data NOT LIKE '{%' AND data NOT LIKE '[%'"
        )->fetchAssociative();

        return (int) ($row['cnt'] ?? 0);
    }

    public function run(Result $result, $offset): Result
    {
        $prefix = $this->getPrefix();
        $rows = elgg()->db->getConnection('read')->executeQuery(
            "SELECT url, data FROM {$prefix}scraper_data WHERE data NOT LIKE '{%' AND data NOT LIKE '[%' LIMIT 50"
        )->fetchAllAssociative();

        if (empty($rows)) {
            $result->addSuccesses(0);
            return $result;
        }

        $write = elgg()->db->getConnection('write');

        foreach ($rows as $row) {
            $decoded = @unserialize($row['data'], ['allowed_classes' => false]);
            if ($decoded === false && $row['data'] !== serialize(false)) {
                $result->addFailure();
                continue;
            }

            $json = json_encode($decoded);
            $write->executeStatement(
                "UPDATE {$prefix}scraper_data SET data = ? WHERE url = ?",
                [$json, $row['url']]
            );
            $result->addSuccesses(1);
        }

        return $result;
    }

    private function getPrefix(): string
    {
        return elgg()->db->prefix;
    }
}
