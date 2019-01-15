<?php
declare(strict_types=1);

namespace PHPSu\Tests\Config;

use PHPSu\Config\DSN;
use PHPUnit\Framework\TestCase;

final class DSNTest extends TestCase
{
    public function testSshWithoutSchema(): void
    {
        $dsn = new DSN('user@host', 'ssh');
        $this->assertSame('ssh', $dsn->getProtocol());
        $this->assertSame('host', $dsn->getHost());
        $this->assertSame('user', $dsn->getUser());
    }

    public function testSshWithSchema(): void
    {
        $dsn = new DSN('ssh://user@host', 'ssh');
        $this->assertSame('host', $dsn->getHost());
        $this->assertSame('user', $dsn->getUser());
        $this->assertSame(22, $dsn->getPort());
    }

    public function testSshWithSchemaPort2206(): void
    {
        $dsn = new DSN('ssh://user@host:2206', 'ssh');
        $this->assertSame('host', $dsn->getHost());
        $this->assertSame('user', $dsn->getUser());
        $this->assertSame(2206, $dsn->getPort());
    }

    public function testMysql(): void
    {
        $dsn = new DSN('mysql://user:pw@host:3307/database', 'mysql');
        $this->assertSame('host', $dsn->getHost());
        $this->assertSame('user', $dsn->getUser());
        $this->assertSame('pw', $dsn->getPassword());
        $this->assertSame('database', $dsn->getPath());
        $this->assertSame(3307, $dsn->getPort());
    }
}
