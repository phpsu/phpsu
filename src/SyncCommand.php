<?php

declare(strict_types=1);

namespace Phpsu\Phpsu;

use DateTimeImmutable;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Query\Expression\CompositeExpression;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\SchemaDiff;
use Doctrine\DBAL\Schema\Table;
use Exception;
use Kanti\SshTunnel\Remote;
use Kanti\SshTunnel\SshTunnel;
use Phpsu\Phpsu\Dbal\Connection;
use Psr\Log\LoggerInterface;

use RuntimeException;
use Throwable;

use function array_diff;
use function array_values;
use function count;
use function dump;
use function hrtime;
use function implode;
use function in_array;
use function max;
use function memory_get_usage;
use function number_format;
use function sleep;
use function strlen;

use const PHP_EOL;

final readonly class SyncCommand
{
    /**
     * @throws MultiThrowableException
     */
    public function __construct(
        private Config $config,
    ) {
        $this->sync();
    }

    /**
     * @throws MultiThrowableException
     */
    private function sync(): void
    {
//        $lastSync = DateTimeImmutable::createFromFormat('U', (string)1733906457); // sys_log ~40 to sync
//        $lastSync = DateTimeImmutable::createFromFormat('U', (string)0); // sys_log all to sync
//        $lastSync = DateTimeImmutable::createFromFormat('U', (string)1733832802); // sys_log ~1000 to sync
        $lastSync = DateTimeImmutable::createFromFormat('U', (string)time()); // current Timestamp

        [$sourceConnection, $destinationConnection] = $this->getConnections();

        $this->removeStuffForDebug($destinationConnection);

        $sourceSchema = $sourceConnection->connection->createSchemaManager()->introspectSchema();

        $tableConfig = $this->getTableConfig($sourceSchema);
//        $tableConfig = ['sys_history' => $tableConfig['sys_history']]; // TODO remove

        $schemaSyncedTables = $this->syncSchema($tableConfig, $sourceSchema, $destinationConnection);

        foreach ($tableConfig as $table) {
            $schemaChanged = in_array($table->name, $schemaSyncedTables);

            // TODO maybe make transitional:
            $this->syncTable($lastSync, $table, $schemaChanged, $sourceConnection, $destinationConnection);
        }
    }

    /**
     * @return array{0: Connection, 1: Connection}
     */
    private function getConnections(): array
    {
        $exceptions = [];
        try {
            $sourceConnection = Connection::create($this->config->getSource());
            $sourceConnection->connection->getServerVersion(); // connect now
        } catch (Throwable $throwable) {
            $exceptions[] = $throwable;
        }

        try {
            $destinationConnection = Connection::create($this->config->getDestination());
            $destinationConnection->connection->getServerVersion(); // connect now
        } catch (Throwable $throwable) {
            $exceptions[] = $throwable;
        }

        MultiThrowableException::throw(...$exceptions);
        return [$sourceConnection, $destinationConnection];
    }

    private function removeStuffForDebug(Connection $destinationConnection): void
    {
        $abstractSchemaManager = $destinationConnection->connection->createSchemaManager();
        if ($abstractSchemaManager->tableExists('cache_rootline') && isset($abstractSchemaManager->listTableColumns('cache_rootline')['content'])) {
            $destinationConnection->connection->executeStatement('ALTER TABLE `cache_rootline` DROP COLUMN `content`;');
        }

        $destinationConnection->connection->executeStatement('DROP TABLE if EXISTS `cache_pages`;');
    }

    /**
     * @return array<string, TableDefinition>
     */
    private function getTableConfig(Schema $sourceSchema): array
    {
//        $this->config->getTableConfig(); // TODO use $timestampColumnDetector and $isVolatileTable from config

        $timestampColumnDetector = function (Table $table): ?string {
            $columns = $table->getColumns();
            $columnsNames = array_map(fn($column) => $column->getName(), $columns);
            foreach (['tstamp', 'ses_tstamp', 'updatedon', 'changed', 'created'] as $timestampColumn) {
                if (in_array($timestampColumn, $columnsNames)) {
                    return $timestampColumn;
                }
            }

            return null;
        };

        $isVolatileTable = function (Table $table): bool {
            $tableName = $table->getName();
            if (str_starts_with($tableName, 'static_')) {
                return true;
            }

            if (str_starts_with($tableName, 'cache_')) {
                return true;
            }

            return in_array($tableName, ['be_sessions', 'fe_sessions', 'sys_http_report', 'sys_messenger_messages', 'sys_preview', 'tx_webp_failed', 'tx_ausio_domain_model_taskstate']);
        };

        $result = [];
        foreach ($sourceSchema->getTables() as $table) {
            $tableName = $table->getName();
            $indexColumns = $table->getPrimaryKey()?->getColumns() ?? [];
            if (count($indexColumns) > 1) {
                $indexColumns = [];
            }

            $primaryKey = $indexColumns[0] ?? null;
            $primaryKeyAutoIncrement = $primaryKey ? $table->getColumn($primaryKey)->getAutoincrement() : false;
            $timestampColumn = $timestampColumnDetector($table);
            $isVolatile = $isVolatileTable($table);
            $result[$tableName] = new TableDefinition($tableName, $primaryKey, $timestampColumn, $primaryKeyAutoIncrement, $isVolatile);

            $colorLightBlue = "\033[94m";
            $colorLightGreen = "\033[92m";
            $colorReset = "\033[0m";
            echo 'Table: ' . $colorLightBlue . str_pad((string) $tableName, 60) . $colorReset
                . ($primaryKey ? (' primaryKey: ' . $colorLightBlue . mb_str_pad($primaryKey . ($primaryKeyAutoIncrement ? '🚗' : ''), 14) . ($primaryKeyAutoIncrement ? '' : ' ') . $colorReset) : '')
                . $colorLightGreen . str_pad((string)$timestampColumn, 10) . $colorReset
                . ($isVolatile ? $colorLightBlue . ' 🫗volatile' . $colorReset : '')
                . PHP_EOL;
        }

        return $result;
    }

    /**
     * @param array<string, TableDefinition> $tableConfig
     * @return list<string>
     */
    private function syncSchema(array $tableConfig, Schema $sourceSchema, Connection $destinationConnection): array
    {
        $updatedTables = [];

        $destinationSchemaManager = $destinationConnection->connection->createSchemaManager();
        $destination = $destinationSchemaManager->introspectSchema();
        $schemaDiff = $destinationSchemaManager->createComparator()->compareSchemas($destination, $sourceSchema);

        $createdTables = [];
        foreach ($schemaDiff->getCreatedTables() as $table) {
            $tableName = $table->getName();
            if (!isset($tableConfig[$tableName])) {
                continue;
            }

            $createdTables[] = $table;
            $updatedTables[] = $tableName;
        }

        $alteredTables = [];
        foreach ($schemaDiff->getAlteredTables() as $table) {
            $tableName = $table->getOldTable()->getName();
            if (!isset($tableConfig[$tableName])) {
                continue;
            }

            $alteredTables[] = $table;
            $updatedTables[] = $tableName;
        }

        $schemaDiff = new SchemaDiff([], [], $createdTables, $alteredTables, [], [], [], []);

        $alterSchemaSQL = $destinationConnection->connection->getDatabasePlatform()->getAlterSchemaSQL($schemaDiff);
        foreach ($alterSchemaSQL as $sql) {
            try {
                $destinationConnection->connection->executeStatement($sql);
            } catch (Exception $e) {
                throw new RuntimeException('Error executing SQL: ' . $sql, 0, $e);
            }
        }

        $arrayUnique = array_unique($updatedTables);
        sort($arrayUnique);
        return $arrayUnique;
    }

    private function syncTable(?DateTimeImmutable $lastSync, TableDefinition $table, bool $schemaChanged, Connection $sourceConnection, Connection $destinationConnection): void
    {
        $colorYellow = "\033[33m";
        $colorReset = "\033[0m";

        if ($table->isVolatile) {
            if ($schemaChanged) {
                echo $colorYellow . 'WARNING: Schema changed for volatile table ' . $table->name . ', will TRUNCATE the table . ' . $colorReset . PHP_EOL;
                $destinationConnection->connection->executeStatement('TRUNCATE TABLE ' . $table->name);
            }

            return;
        }

        if ($schemaChanged) {
            echo $colorYellow . 'WARNING: Schema changed for table ' . $table->name . ', doing full sync' . $colorReset . PHP_EOL;
            $this->syncData($table, $sourceConnection, $destinationConnection);
            return;
        }

        if (!$lastSync) {
            echo $colorYellow . 'WARNING: No last sync found, doing full sync' . $colorReset . PHP_EOL;
            $this->syncData($table, $sourceConnection, $destinationConnection);
            return;
        }

        if ($table->timestampColumn) {
            $this->syncDataIncremental($lastSync, $table, $sourceConnection, $destinationConnection);
            return;
        }

        $this->syncData($table, $sourceConnection, $destinationConnection);
    }

    private function syncData(TableDefinition $table, Connection $sourceConnection, Connection $destinationConnection, string|CompositeExpression|null $condition = null): void
    {
        // chunked inserts are faster than single inserts
        // we chunk the fetch as well so we don't run out of memory

        $colorYellow = "\033[33m";
        $colorLightBlue = "\033[94m";
        $colorReset = "\033[0m";
        if (!$condition) {
            echo 'Full sync ' . $colorYellow . $table->name . $colorReset;
        }

        $total = $sourceConnection->count($table->name, $condition);
        echo $colorLightBlue . ' (' . number_format($total, 0, '.', '_') . ')' . $colorReset . PHP_EOL;

        $count = 0;
        $timeUntilNow = 0;
        $this->removeDeletedRows($table, $sourceConnection, $destinationConnection);
        if (!$total) {
            echo 'No rows to sync' . PHP_EOL;
            return;
        }

        echo 'start fetching (if this takes long the memory is probably full with data 😿)' . PHP_EOL;
        $time = hrtime(true);
        // TODO check if this looks the table?
        $sourceQuery = $sourceConnection->select($table->name, $condition);
        $timeToFinish = (hrtime(true) - $time) / 1e9;
        echo sprintf('fetch started %.3fs', $timeToFinish) . PHP_EOL;

        $startTime = hrtime(true);
        while ($count < $total) {
            $rows = [];
            $chunkSize = null;
            while ($row = $sourceQuery->fetchAssociative()) {
                $rows[] = $row;

                $chunkSize ??= $this->estimateBestChunkSize($row);
                if (count($rows) >= $chunkSize) {
                    break;
                }
            }

            $destinationConnection->insertRows($table->name, $rows);
            $count += count($rows);

            // output stuff:
            $x = hrtime(true) - $startTime;
            $timeForLastChunk = $x - $timeUntilNow;
            $timeUntilNow = $x;
            $timeUntilFinished = $timeUntilNow * max($total / $count, 1);
            $timeLeft = $timeUntilFinished - $timeUntilNow;
            $length = strlen((string)$total);
            echo sprintf(
                '%' . $length . 'd/%d %7.3fs 🔜 %5.1fs %6.1fus(current per row) %6.1fus(overall per row) %dMB' . PHP_EOL,
                $count,
                $total,
                $timeUntilNow / 1e9,
                $timeLeft / 1e9,
                $timeForLastChunk / count($rows) / 1e3,
                $timeUntilNow / $count / 1e3,
                memory_get_usage() / 1024 / 1024,
            );
        }

        echo PHP_EOL;
    }

    private function syncDataIncremental(DateTimeImmutable $lastSync, TableDefinition $table, Connection $sourceConnection, Connection $destinationConnection): void
    {
        $colorYellow = "\033[33m";
        $colorReset = "\033[0m";

        $tableHasIdAndChangedAtColumn = true; // TODO
        $idColumn = $table->primaryKey;
        $changedColumn = $table->timestampColumn;
        $changedType = 'int'; // TODO int or datetime

        if (!$tableHasIdAndChangedAtColumn) {
            echo $colorYellow . 'WARNING: no ' . $idColumn . ' and ' . $changedColumn . ' column found for table ' . $table->name . $colorReset . PHP_EOL;
            $this->syncData($table, $sourceConnection, $destinationConnection);
            return;
        }

        echo 'Incremental sync ' . $colorYellow . $table->name . $colorReset;

        // TODO keep timestamp of last sync for each table, so we know what to sync next time
        // for the timestamp we also need to keep last source (application) (if application is a mismatch, make fullSync)
        // if we do not have last sync timestamp, we do a full sync
        // if the table doesn't have a timestamp column, we do a full sync + WARNING for the user


        $queryBuilder = $destinationConnection->connection->createQueryBuilder();
        $expressionBuilder = $queryBuilder->expr();
        if ($changedType === 'int') {
            $condition = $expressionBuilder->gte($changedColumn, (string)$lastSync->getTimestamp());
        } else {
            $condition = $expressionBuilder->gte($changedColumn, $destinationConnection->connection->quote($lastSync->format('Y-m-d H:i:s')));
        }

        $this->syncData($table, $sourceConnection, $destinationConnection, $condition);
    }

    private function removeDeletedRows(TableDefinition $table, Connection $sourceConnection, Connection $destinationConnection): void
    {
        $colorYellow = "\033[33m";
        $colorReset = "\033[0m";

        if ($table->primaryKey === null) {
            echo $colorYellow . 'WARNING: no primary key found for table ' . $table->name . ', will TRUNCATE the table.' . $colorReset . PHP_EOL;
            $destinationConnection->truncate($table->name);
            return;
        }

        $time = hrtime(true);
        $sourceUids = $sourceConnection->select($table->name, null, $table->primaryKey)->fetchFirstColumn();
        $destinationUids = $destinationConnection->select($table->name, null, $table->primaryKey)->fetchFirstColumn();
        $timeToFinish = (hrtime(true) - $time) / 1e9;
        echo sprintf('Getting uids (source %d + dest %d): %.3fs', count($sourceUids), count($destinationUids), $timeToFinish) . PHP_EOL;

        if ($sourceUids && $table->primaryKeyAutoIncrement) {
            $autoIncrement = max($sourceUids) + 1;
            echo 'Setting AUTO_INCREMENT to ' . $autoIncrement . PHP_EOL;
            $destinationConnection->connection->executeStatement('ALTER TABLE ' . $table->name . ' AUTO_INCREMENT = ' . $autoIncrement); // TODO make this work with pgsql, sqlite, etc
        }

        $idsToDelete = array_values(array_diff($destinationUids, $sourceUids));
        if (!$idsToDelete) {
            echo 'No rows to delete from ' . $table->name . PHP_EOL;
            return;
        }

        echo $colorYellow . 'Deleting ' . count($idsToDelete) . ' rows from ' . $table->name . $colorReset . PHP_EOL;

        $queryBuilder = $destinationConnection->connection->createQueryBuilder();
        $queryBuilder
            ->delete($table->name)
            ->where($queryBuilder->expr()->in($table->primaryKey, ':ids'))
            ->setParameter('ids', $idsToDelete, ArrayParameterType::STRING)
            ->executeStatement();
    }

    /**
     * 1000 chunkSize for 1000 strlen => fastest. so we calculate the chunkSize based on the row count (best estimation)
     */
    private function estimateBestChunkSize(array $row): int
    {
        // 100 => 180us per row
        // 500 => 115us per row
        // 1000 => 99us per row (normal row with ~10 columns)
        // 1500 => 130us per row
        // 10_000 => 999us per row

        $strlen = strlen(implode('', $row));
        $optimalStrlen = 1000;
        $optimalRowCount = 1000;
        $calculatedChunkSize = (int)($optimalRowCount * $optimalStrlen / $strlen);
        return min(max(10, $calculatedChunkSize), 1000);
    }
}
