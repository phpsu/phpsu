<?php

declare(strict_types=1);

namespace Phpsu\Phpsu\Dbal;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Schema\Identifier;

use function sprintf;

final readonly class BulkInsert
{
    public function insert(Connection $connection, string $table, array $rows, array $types = []): int
    {
        if ($rows === []) {
            return 0;
        }

        $sql = $this->sql($connection->getDatabasePlatform(), new Identifier($table), $rows);

        return $connection->executeStatement($sql, $this->parameters($rows), $this->types($types, count($rows)));
    }

    private function sql(AbstractPlatform $platform, Identifier $table, array $rows): string
    {
        $columns = $this->quoteColumns($platform, $this->extractColumns($rows));

        // insert or update:
//        $mySql = /** @lang MySQL */
//            "
//INSERT INTO phpsu_test_dest.dummy (uid, name, tstamp)
//VALUES (3, 'test', 2),
//       (4, 'test2', UNIX_TIMESTAMP()) as excluded
//ON DUPLICATE KEY UPDATE
//       name = excluded.name, tstamp = excluded.tstamp;
//";
//        $sqlite = $pgSql = /** @lang PostgreSQL */
//            "INSERT INTO phpsu_test_dest.dummy (uid, name, tstamp)
//VALUES (3, 'test', 2),
//       (4, 'test2', 1500)
//ON CONFLICT (uid) DO UPDATE SET
//       name = excluded.name, tstamp = excluded.tstamp;";

        $sql = sprintf(
            'INSERT INTO %s %s VALUES %s as excluded ON DUPLICATE KEY UPDATE %s;',
            $table->getQuotedName($platform),
            $this->stringifyColumns($columns),
            $this->generatePlaceholders(count($columns), count($rows)),
            implode(', ', array_map(static fn (string $column): string => sprintf('%s = excluded.%s', $column, $column), $columns))
        );
        return $sql;
    }

    private function extractColumns(array $dataset): array
    {
        if ($dataset === []) {
            return [];
        }

        $first = reset($dataset);

        return array_keys($first);
    }

    private function quoteColumns(AbstractPlatform $platform, array $columns): array
    {
        $mapper = static fn (string $column): string => (new Identifier($column))->getQuotedName($platform);

        return array_map($mapper, $columns);
    }

    private function stringifyColumns(array $columns): string
    {
        return $columns === [] ? '' : sprintf('(%s)', implode(', ', $columns));
    }

    private function generatePlaceholders(int $columnsLength, int $datasetLength): string
    {
        // (?, ?, ?, ?)
        $placeholders = sprintf('(%s)', implode(', ', array_fill(0, $columnsLength, '?')));

        // (?, ?), (?, ?)
        return implode(', ', array_fill(0, $datasetLength, $placeholders));
    }

    private function parameters(array $dataset): array
    {
        $reducer = static fn (array $flattenedValues, array $dataset): array => array_merge($flattenedValues, array_values($dataset));

        return array_reduce($dataset, $reducer, []);
    }

    private function types(array $types, int $datasetLength): array
    {
        if ($types === []) {
            return [];
        }

        $types = array_values($types);

        $positionalTypes = [];

        for ($idx = 1; $idx <= $datasetLength; $idx++) {
            $positionalTypes = array_merge($positionalTypes, $types);
        }

        return $positionalTypes;
    }
}
