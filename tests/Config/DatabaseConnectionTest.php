<?php
declare(strict_types=1);

namespace PHPSu\Tests\Config;

use PHPSu\Config\Database;
use PHPSu\Config\DatabaseConnection;
use PHPUnit\Framework\TestCase;

class DatabaseConnectionTest extends TestCase
{

    public function testFromDatabaseObject()
    {
        $database = new Database();
        $database->setName('Database');
        $database->setUrl('Url');
        $connection = DatabaseConnection::fromDatabaseObject($database);
        $this->assertEquals('Database', $connection->getIdentifier());
    }
}
