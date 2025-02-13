<?php

declare(strict_types=1);

namespace Phpsu\Phpsu\Dbal;

use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Query\Expression\CompositeExpression;
use Doctrine\DBAL\Result;
use PDO;

use function in_array;

final readonly class Connection
{
    public function __construct(
        public \Doctrine\DBAL\Connection $connection,
        private BulkInsert $bulkInsert = new BulkInsert(),
    ) {
    }

    /**
     * @param array<mixed> $config
     */
    public static function create(array $config): self
    {
        // TODO test with sqlite mysql mariadb postgresql ...?
        // TYPO3 Supports:
        // MariaDB >= 10.4.3 <= 11.0.0
        // MySQL >= 8.0.17
        // PostgreSQL >= 10.0
        // SQLite >= 3.8.3
        // doctrine supports:

        // MySQL
        // MySQLPlatform for version 5.7.9 and above.
        // MySQL80Platform for version 8.0 and above.
        // MySQL84Platform for version 8.4 and above.
        //
        // MariaDB
        // MariaDBPlatform for version 10.4.3 and above.
        // MariaDB1052Platform for version 10.5.2 and above.
        // MariaDB1060Platform for version 10.6 and above.
        // MariaDB1010Platform for version 10.10 and above.
        //
        // Oracle
        // OraclePlatform for version 18c (12.2.0.2) and above.
        //
        // Microsoft SQL Server
        // SQLServerPlatform for version 2017 and above.
        //
        // PostgreSQL
        // PostgreSQLPlatform for version 10.0 and above.
        // PostgreSQL120Platform for version 12.0 and above.
        //
        // IBM DB2
        // Db2Platform for all versions.
        //
        // SQLite
        // SQLitePlatform for all versions.


        // connection type: sqlite                      -> PDO
        // connection type: direct TCP                  -> PDO
        // connection type: socket connection           -> PDO
        // connection type: docker container            -> docker inspect (get IP) + PDO
        // telnet $(docker inspect -f '{{range.NetworkSettings.Networks}}{{.IPAddress}}{{end}}' global-global-db-v8-1) 3306

        // connection type: ssh + sqlite                -> SSHFS + PDO ??? how should we make this work as in container SSHFS is not working (no FUSE)
        // connection type: ssh + socket connection     -> ssh tunnel + PDO
        // connection type: ssh + TCP                   -> ssh tunnel + PDO
        // connection type: ssh + docker container      -> ssh tunnel + docker inspect (get IP) + PDO

        $config['driverOptions'] = [
            PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => false,  // Disable MySQL client-side buffering
            PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL,       // Use unbuffered cursor
        ];

        $configuration = new Configuration();
        $configuration->setMiddlewares([]);

        if (isset($config['dbname'])) {
            $dbname = $config['dbname'];
            unset($config['dbname']);
            $conn = DriverManager::getConnection($config, $configuration);
            $schemaManager = $conn->createSchemaManager();// create database if not exists
            if (!in_array($dbname, $schemaManager->listDatabases())) {
                $schemaManager->createDatabase($dbname);
            }

            $config['dbname'] = $dbname;
            $conn->close();
        }

        $conn = DriverManager::getConnection($config, $configuration);
        return new Connection($conn);
    }

    /**
     * @param list<array<mixed>> $rows
     */
    public function insertRows(string $tableName, array $rows): int
    {
        return $this->bulkInsert->insert($this->connection, $tableName, $rows);
    }

    public function truncate(string $tableName): int
    {
        $platform = $this->connection->getDatabasePlatform();
        return $this->connection->executeStatement($platform->getTruncateTableSQL($tableName, true));
    }

    public function count(string $tableName, string|CompositeExpression|null $condition = null): int
    {
        $queryBuilder = $this->connection
            ->createQueryBuilder()
            ->select('COUNT(*)')
            ->from($tableName);
        if ($condition) {
            $queryBuilder = $queryBuilder->where($condition);
        }

        return $queryBuilder
            ->executeQuery()
            ->fetchOne();
    }

    public function select(string $tableName, CompositeExpression|string|null $condition, string $selectPart = '*'): Result
    {
        $queryBuilder = $this->connection
            ->createQueryBuilder()
            ->select($selectPart)
            ->from($tableName);
        if ($condition) {
            $queryBuilder = $queryBuilder->where($condition);
        }

        return $queryBuilder
            ->executeQuery();
    }
}
