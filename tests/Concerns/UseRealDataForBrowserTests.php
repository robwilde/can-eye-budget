<?php /** @noinspection SqlWithoutWhere */

declare(strict_types=1);

namespace Tests\Concerns;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use PDO;

trait UseRealDataForBrowserTests
{
    protected function copyRealDatabaseForBrowserTest(): void
    {
        $realDbPath = database_path('database.sqlite');

        if (! File::exists($realDbPath)) {
            // If no real database exists, just proceed with factory data
            return;
        }

        // Copy data from real database to test database
        $realDb = new PDO('sqlite:'.$realDbPath);
        $testDb = DB::connection()->getPdo();

        // Get list of all tables from real database
        $tables = $realDb->query("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'")->fetchAll(PDO::FETCH_COLUMN);

        foreach ($tables as $table) {
            try {
                // Skip migrations table to avoid conflicts
                if ($table === 'migrations') {
                    continue;
                }

                // Clear existing test data
                $testDb->exec("DELETE FROM `$table`");

                // Copy all data from real database
                $rows = $realDb->query("SELECT * FROM `$table`")->fetchAll(PDO::FETCH_ASSOC);

                if (empty($rows)) {
                    continue;
                }

                // Get column names
                $columns = array_keys($rows[0]);
                $placeholders = str_repeat('?,', count($columns) - 1).'?';

                $stmt = $testDb->prepare("INSERT INTO `$table` (`".implode('`, `', $columns)."`) VALUES ($placeholders)");

                foreach ($rows as $row) {
                    $stmt->execute(array_values($row));
                }

            } catch (Exception $e) {
                // Skip tables that might have issues, but don't fail the test
                continue;
            }
        }

        // Reset auto-increment sequences to avoid ID conflicts
        foreach ($tables as $table) {
            try {
                $maxId = $testDb->query("SELECT MAX(id) FROM `$table`")->fetchColumn();
                if ($maxId) {
                    $testDb->exec("UPDATE sqlite_sequence SET seq = $maxId WHERE name = '$table'");
                }
            } catch (Exception $e) {
                // Some tables might not have id columns
                continue;
            }
        }
    }
}
