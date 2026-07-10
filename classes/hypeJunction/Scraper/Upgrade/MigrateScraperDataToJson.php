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
                // Neither JSON nor valid serialized data. Two bugs lived here:
                // Result::addFailure() does not exist (it is addFailures()), so this
                // threw a fatal; and even if it had not, countItems() counts rows that
                // are not yet JSON, so a row that can never BECOME JSON would keep the
                // count above zero and the batch would loop forever.
                //
                // elgg_scraper_data is a regenerable link-preview cache. Drop the row.
                $write->executeStatement(
                    "DELETE FROM {$prefix}scraper_data WHERE url = ?",
                    [$row['url']]
                );
                elgg_log("hypeScraper: dropped unparseable scraper_data row url={$row['url']}", \Psr\Log\LogLevel::NOTICE);
                $result->addSuccesses();
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
