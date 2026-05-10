<?php

namespace hypeJunction\Scraper\Upgrade;

use Elgg\Upgrade\Batch;
use Elgg\Upgrade\Result;

/**
 * Re-encodes scraper_data rows from PHP serialize() to JSON (5.x migration).
 */
class MigrateScraperDataToJson implements Batch
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
        $rows = elgg()->db->getData("SELECT COUNT(*) AS cnt FROM {$this->getPrefix()}scraper_data WHERE data NOT LIKE '{%' AND data NOT LIKE '[%'");
        return $rows[0]->cnt ?? 0;
    }

    public function run(Result $result, $offset): Result
    {
        $prefix = $this->getPrefix();
        $rows = elgg()->db->getData(
            "SELECT url, data FROM {$prefix}scraper_data WHERE data NOT LIKE '{%' AND data NOT LIKE '[%' LIMIT 50"
        );

        if (empty($rows)) {
            $result->addSuccesses(0);
            return $result;
        }

        foreach ($rows as $row) {
            $decoded = @unserialize($row->data, ['allowed_classes' => false]);
            if ($decoded === false && $row->data !== serialize(false)) {
                $result->addFailure();
                continue;
            }

            $json = json_encode($decoded);
            elgg()->db->updateData(
                "UPDATE {$prefix}scraper_data SET data = ? WHERE url = ?",
                false,
                [$json, $row->url]
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
