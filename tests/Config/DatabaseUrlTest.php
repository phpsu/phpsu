<?php

declare(strict_types=1);

namespace PHPSu\Tests\Config;

use PHPSu\Config\DatabaseUrl;
use PHPUnit\Framework\TestCase;

class DatabaseUrlTest extends TestCase
{
    public function testInvalidUrl()
    {
        $this->expectExceptionMessageRegExp('/DatabaseUrl could not been parsed/');
        new DatabaseUrl('://:/:/:/:/');
    }

    public function testInvalidUser()
    {
        $this->expectExceptionMessageRegExp('/User must be set/');
        new DatabaseUrl('test');
    }

    public function testInvalidPort()
    {
        $dsn = new DatabaseUrl('user@test');
        $this->expectExceptionMessage('port must be between 0 and 65535');
        $dsn->setPort(0);
    }

    public function testInvalidPort2()
    {
        $dsn = new DatabaseUrl('user@test');
        $this->expectExceptionMessage('port must be between 0 and 65535');
        $dsn->setPort(65535);
    }

    public function testInvalidHost()
    {
        $dsn = new DatabaseUrl('user@test');
        $this->expectExceptionMessage('host ho/st has invalid character');
        $dsn->setHost('ho/st');
    }

    public function testInvalidHost2()
    {
        $dsn = new DatabaseUrl('user@test');
        $this->expectExceptionMessage('Host must be set');
        $dsn->setHost('');
    }

    public function testMinimumValidPort()
    {
        $dsn = new DatabaseUrl('user@test');
        $dsn->setPort(1);
        $this->assertSame(1, $dsn->getPort());
    }

    public function testMaximumValidPort()
    {
        $dsn = new DatabaseUrl('user@test');
        $dsn->setPort(65534);
        $this->assertSame(65534, $dsn->getPort());
    }

    public function testSshWithoutSchema()
    {
        $dsn = new DatabaseUrl('user@host');
        $this->assertSame('user', $dsn->getUser());
        $this->assertSame('', $dsn->getPassword());
        $this->assertSame('host', $dsn->getHost());
        $this->assertSame(3306, $dsn->getPort());
        $this->assertSame('mysql://user@host', $dsn->__toString());
    }

    public function testSshWithSchema()
    {
        $dsn = new DatabaseUrl('mysql://user@host');
        $this->assertSame('user', $dsn->getUser());
        $this->assertSame('', $dsn->getPassword());
        $this->assertSame('host', $dsn->getHost());
        $this->assertSame(3306, $dsn->getPort());
        $this->assertSame('mysql://user@host', $dsn->__toString());
    }

    public function testSshWithSchemaPort2206()
    {
        $dsn = new DatabaseUrl('mysql://user@host:2206');
        $this->assertSame('user', $dsn->getUser());
        $this->assertSame('', $dsn->getPassword());
        $this->assertSame('host', $dsn->getHost());
        $this->assertSame(2206, $dsn->getPort());
        $this->assertSame('mysql://user@host:2206', $dsn->__toString());
    }

    public function testSshWithPassword()
    {
        $dsn = new DatabaseUrl('mysql://user:password@host');
        $this->assertSame('user', $dsn->getUser());
        $this->assertSame('password', $dsn->getPassword());
        $this->assertSame('host', $dsn->getHost());
        $this->assertSame(3306, $dsn->getPort());
        $this->assertSame('mysql://user:password@host', $dsn->__toString());
    }

    public function testSshWithIp()
    {
        $dsn = new DatabaseUrl('user@192.168.0.1');
        $this->assertSame('user', $dsn->getUser());
        $this->assertSame('', $dsn->getPassword());
        $this->assertSame('192.168.0.1', $dsn->getHost());
        $this->assertSame(3306, $dsn->getPort());
        $this->assertSame('mysql://user@192.168.0.1', $dsn->__toString());
    }

    public function testDatabaseUrlGetter()
    {
        $dsn = new DatabaseUrl('user@192.168.0.1/database');
        $this->assertSame('user', $dsn->getUser());
        $this->assertSame('database', $dsn->getDatabase());
        $this->assertSame('', $dsn->getPassword());
        $this->assertSame('192.168.0.1', $dsn->getHost());
        $this->assertSame(3306, $dsn->getPort());
        $this->assertSame('mysql://user@192.168.0.1', $dsn->__toString());
        $dsn->setUser('user2');
        $dsn->setDatabase('database2');
        $dsn->setPassword('pw2');
        $dsn->setHost('host2');
        $dsn->setPort(2298);
        $this->assertSame('user2', $dsn->getUser());
        $this->assertSame('database2', $dsn->getDatabase());
        $this->assertSame('pw2', $dsn->getPassword());
        $this->assertSame('host2', $dsn->getHost());
        $this->assertSame(2298, $dsn->getPort());
        $this->assertSame('mysql://user2:pw2@host2:2298', $dsn->__toString());
    }
}
