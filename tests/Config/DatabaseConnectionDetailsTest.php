<?php

declare(strict_types=1);

namespace PHPSu\Tests\Config;

use Exception;
use PHPSu\Config\DatabaseConnectionDetails;
use PHPUnit\Framework\TestCase;

class DatabaseConnectionDetailsTest extends TestCase
{
    public function testInvalidUrl(): void
    {
        $this->expectExceptionMessageMatches('/DatabaseUrl could not been parsed/');
        DatabaseConnectionDetails::fromUrlString('mysql://:/:/:/:/');
    }

    public function testInvalidUser(): void
    {
        $this->expectExceptionMessageMatches('/User must be set/');
        DatabaseConnectionDetails::fromUrlString('test');
    }

    public function testInvalidPort(): void
    {
        $connectionDetails = DatabaseConnectionDetails::fromUrlString('user@test');
        $this->expectExceptionMessage('port must be between 0 and 65535');
        $connectionDetails->setPort(0);
    }

    public function testInvalidPort2(): void
    {
        $connectionDetails = DatabaseConnectionDetails::fromUrlString('user@test');
        $this->expectExceptionMessage('port must be between 0 and 65535');
        $connectionDetails->setPort(65535);
    }

    public function testInvalidHost(): void
    {
        $connectionDetails = DatabaseConnectionDetails::fromUrlString('user@test');
        $this->expectExceptionMessage('host ho/st has invalid character');
        $connectionDetails->setHost('ho/st');
    }

    public function testInvalidHost2(): void
    {
        $connectionDetails = DatabaseConnectionDetails::fromUrlString('user@test');
        $this->expectExceptionMessage('Host must be set');
        $connectionDetails->setHost('');
    }

    public function testMinimumValidPort(): void
    {
        $connectionDetails = DatabaseConnectionDetails::fromUrlString('user@test');
        $connectionDetails->setPort(1);
        $this->assertSame(1, $connectionDetails->getPort());
    }

    public function testMaximumValidPort(): void
    {
        $connectionDetails = DatabaseConnectionDetails::fromUrlString('user@test');
        $connectionDetails->setPort(65534);
        $this->assertSame(65534, $connectionDetails->getPort());
    }

    public function testPasswordWithHash(): void
    {
        $connectionDetails = DatabaseConnectionDetails::fromUrlString('user@test');
        $connectionDetails->setPassword('test#password');
        $this->assertSame('test#password', $connectionDetails->getPassword());
    }

    public function testSshWithoutSchema(): void
    {
        $connectionDetails = DatabaseConnectionDetails::fromUrlString('user@host');
        $this->assertSame('user', $connectionDetails->getUser());
        $this->assertSame('', $connectionDetails->getPassword());
        $this->assertSame('host', $connectionDetails->getHost());
        $this->assertSame(3306, $connectionDetails->getPort());
        $this->assertSame('mysql://user@host', $connectionDetails->__toString());
    }

    public function testSshWithSchema(): void
    {
        $connectionDetails = DatabaseConnectionDetails::fromUrlString('mysql://user@host');
        $this->assertSame('user', $connectionDetails->getUser());
        $this->assertSame('', $connectionDetails->getPassword());
        $this->assertSame('host', $connectionDetails->getHost());
        $this->assertSame(3306, $connectionDetails->getPort());
        $this->assertSame('mysql://user@host', $connectionDetails->__toString());
    }

    public function testSshWithSchemaPort2206(): void
    {
        $connectionDetails = DatabaseConnectionDetails::fromUrlString('mysql://user@host:2206');
        $this->assertSame('user', $connectionDetails->getUser());
        $this->assertSame('', $connectionDetails->getPassword());
        $this->assertSame('host', $connectionDetails->getHost());
        $this->assertSame(2206, $connectionDetails->getPort());
        $this->assertSame('mysql://user@host:2206', $connectionDetails->__toString());
    }

    public function testSshWithPassword(): void
    {
        $connectionDetails = DatabaseConnectionDetails::fromUrlString('mysql://user:password@host/database');
        $this->assertSame('user', $connectionDetails->getUser());
        $this->assertSame('password', $connectionDetails->getPassword());
        $this->assertSame('host', $connectionDetails->getHost());
        $this->assertSame(3306, $connectionDetails->getPort());
        $this->assertSame('mysql://user:password@host/database', $connectionDetails->__toString());
    }

    public function testSshWithIp(): void
    {
        $connectionDetails = DatabaseConnectionDetails::fromUrlString('user@192.168.0.1');
        $this->assertSame('user', $connectionDetails->getUser());
        $this->assertSame('', $connectionDetails->getPassword());
        $this->assertSame('192.168.0.1', $connectionDetails->getHost());
        $this->assertSame(3306, $connectionDetails->getPort());
        $this->assertSame('mysql://user@192.168.0.1', $connectionDetails->__toString());
    }

    public function testDatabaseConnectionDetailsGetter(): void
    {
        $connectionDetails = DatabaseConnectionDetails::fromUrlString('user@192.168.0.1/database');
        $this->assertSame('user', $connectionDetails->getUser());
        $this->assertSame('database', $connectionDetails->getDatabase());
        $this->assertSame('', $connectionDetails->getPassword());
        $this->assertSame('192.168.0.1', $connectionDetails->getHost());
        $this->assertSame(3306, $connectionDetails->getPort());
        $connectionDetails->setUser('user2');
        $connectionDetails->setDatabase('database2');
        $connectionDetails->setPassword('pw2');
        $connectionDetails->setHost('host2');
        $connectionDetails->setPort(2298);
        $this->assertSame('user2', $connectionDetails->getUser());
        $this->assertSame('database2', $connectionDetails->getDatabase());
        $this->assertSame('pw2', $connectionDetails->getPassword());
        $this->assertSame('host2', $connectionDetails->getHost());
        $this->assertSame(2298, $connectionDetails->getPort());
        $this->assertSame('mysql://user2:pw2@host2:2298/database2', $connectionDetails->__toString());
    }

    public function testDatabaseType(): void
    {
        $connectionDetails = DatabaseConnectionDetails::fromUrlString('mysql://user@192.168.0.1');
        $this->assertSame('mysql', $connectionDetails->getDatabaseType());

        $connectionDetails = DatabaseConnectionDetails::fromUrlString('mariadb://user@192.168.0.1');
        $this->assertSame('mariadb', $connectionDetails->getDatabaseType());

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Database Type must be mysql or mariadb');
        DatabaseConnectionDetails::fromUrlString('myCustomDb://user@192.168.0.1');
    }
}
