<?php

declare(strict_types=1);

namespace Phpsu\Phpsu;

use Kanti\Secrets\Secrets;
use PDO;

final readonly class Config
{
    public function getTableConfig(): array
    {
        return [
            'full' => ['.*'],
            'incremental' => [
                'tstmp' => '.*',
            ],
        ];
    }

    public function getSource(): array
    {
        //integration 02
        return [
            'driver' => 'pdo_mysql',
            'host' => '127.0.0.1',
            'user' => 'root',
            'password' => Secrets::convert('wBefnkAcmHHiG9sVQscAxmWX9fadQ2JmLuc4GB7JECoGwL3VLp4NmFKSpT3a'), // TODO encrypt secretes automatically (instead of storing them in plain text)
//            'dbname' => 'phpsu_test_source',
            'dbname' => '339005_rampf',
            'port' => 3306,
            'driverOptions' => [
                PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => false,  // Disable MySQL client-side buffering
                PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL,       // Use unbuffered cursor
            ]
        ];
    }

    public function getDestination(): array
    {
//        return [
//            'driver' => 'pdo_sqlite',
//            'path' => __DIR__ . '/../phpsu_test_dest.db',
//            'driverOptions' => [
//                PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => false,  // Disable MySQL client-side buffering
//                PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL,       // Use unbuffered cursor
//            ]
//        ];
        return [
            'driver' => 'pdo_mysql',
            'host' => 'global-global-db-v8-1',
            'user' => 'root',
            'password' => 'root',
            'dbname' => 'phpsu_test_dest',
            'port' => 3306,
            'driverOptions' => [
                PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => false,  // Disable MySQL client-side buffering
                PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL,       // Use unbuffered cursor
            ]
        ];
    }
}
