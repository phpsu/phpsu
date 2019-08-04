<?php
declare(strict_types=1);

namespace PHPSu\Tests\Config;

use PHPSu\Config\SqlDatabaseConfiguration;
use PHPUnit\Framework\TestCase;

class SqlDatabaseConfigurationTest extends TestCase
{
    public function testInvalidUrl()
    {
        $this->expectExceptionMessageRegExp('/SqlDatabaseConfiguration could not been parsed/');
        new SqlDatabaseConfiguration('://:/:/:/:/');
    }

    public function testInvalidUser()
    {
        $this->expectExceptionMessageRegExp('/User must be set/');
        new SqlDatabaseConfiguration('test');
    }

    public function testInvalidPort()
    {
        $dsn = new SqlDatabaseConfiguration('user@test');
        $this->expectExceptionMessage('port must be between 0 and 65535');
        $dsn->setPort(0);
    }

    public function testInvalidPort2()
    {
        $dsn = new SqlDatabaseConfiguration('user@test');
        $this->expectExceptionMessage('port must be between 0 and 65535');
        $dsn->setPort(65535);
    }

    public function testInvalidHost()
    {
        $dsn = new SqlDatabaseConfiguration('user@test');
        $this->expectExceptionMessage('host ho/st has invalid character');
        $dsn->setHost('ho/st');
    }

    public function testInvalidHost2()
    {
        $dsn = new SqlDatabaseConfiguration('user@test');
        $this->expectExceptionMessage('Host must be set');
        $dsn->setHost('');
    }

    public function testMinimumValidPort()
    {
        $dsn = new SqlDatabaseConfiguration('user@test');
        $dsn->setPort(1);
        $this->assertSame(1, $dsn->getPort());
    }

    public function testMaximumValidPort()
    {
        $dsn = new SqlDatabaseConfiguration('user@test');
        $dsn->setPort(65534);
        $this->assertSame(65534, $dsn->getPort());
    }

    public function testSshWithoutSchema()
    {
        $dsn = new SqlDatabaseConfiguration('user@host');
        $this->assertSame('user', $dsn->getUser());
        $this->assertSame('', $dsn->getPassword());
        $this->assertSame('host', $dsn->getHost());
        $this->assertSame(3306, $dsn->getPort());
        $this->assertSame('mysql://user@host', (string)$dsn);
    }

    public function testSshWithSchema()
    {
        $dsn = new SqlDatabaseConfiguration('mysql://user@host');
        $this->assertSame('user', $dsn->getUser());
        $this->assertSame('', $dsn->getPassword());
        $this->assertSame('host', $dsn->getHost());
        $this->assertSame(3306, $dsn->getPort());
        $this->assertSame('mysql://user@host', (string)$dsn);
    }

    public function testSshWithSchemaPort2206()
    {
        $dsn = new SqlDatabaseConfiguration('mysql://user@host:2206');
        $this->assertSame('user', $dsn->getUser());
        $this->assertSame('', $dsn->getPassword());
        $this->assertSame('host', $dsn->getHost());
        $this->assertSame(2206, $dsn->getPort());
        $this->assertSame('mysql://user@host:2206', (string)$dsn);
    }

    public function testSshWithPassword()
    {
        $dsn = new SqlDatabaseConfiguration('mysql://user:password@host');
        $this->assertSame('user', $dsn->getUser());
        $this->assertSame('password', $dsn->getPassword());
        $this->assertSame('host', $dsn->getHost());
        $this->assertSame(3306, $dsn->getPort());
        $this->assertSame('mysql://user:password@host', (string)$dsn);
    }

    public function testSshWithIp()
    {
        $dsn = new SqlDatabaseConfiguration('user@192.168.0.1');
        $this->assertSame('user', $dsn->getUser());
        $this->assertSame('', $dsn->getPassword());
        $this->assertSame('192.168.0.1', $dsn->getHost());
        $this->assertSame(3306, $dsn->getPort());
        $this->assertSame('mysql://user@192.168.0.1', (string)$dsn);
    }

    public function testDatabaseUrlGetter()
    {
        $dsn = new SqlDatabaseConfiguration('user@192.168.0.1');
        $this->assertSame('user', $dsn->getUser());
        $this->assertSame('', $dsn->getPassword());
        $this->assertSame('192.168.0.1', $dsn->getHost());
        $this->assertSame(3306, $dsn->getPort());
        $this->assertSame('mysql://user@192.168.0.1', (string)$dsn);
        $dsn->setUser('user2');
        $dsn->setPassword('pw2');
        $dsn->setHost('host2');
        $dsn->setPort(2298);
        $this->assertSame('user2', $dsn->getUser());
        $this->assertSame('pw2', $dsn->getPassword());
        $this->assertSame('host2', $dsn->getHost());
        $this->assertSame(2298, $dsn->getPort());
        $this->assertSame('mysql://user2:pw2@host2:2298', (string)$dsn);
    }
}
