<?php
declare(strict_types=1);

namespace PHPSu\Tests\Config;

use PHPSu\Config\SshUrl;
use PHPUnit\Framework\TestCase;

final class SshUrlTest extends TestCase
{
    public function testInvalidUrl(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageRegExp('/SshUrl could not been parsed/');
        new SshUrl('://:/:/:/:/');
    }

    public function testInvalidUser(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageRegExp('/User must be set/');
        new SshUrl('test');
    }

    public function testInvalidPort(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageRegExp('/port must be between 0 and 65535/');
        $dsn = new SshUrl('user@test');
        $dsn->setPort(1234567);
    }

    public function testSshWithoutSchema(): void
    {
        $dsn = new SshUrl('user@host');
        $this->assertSame('user', $dsn->getUser());
        $this->assertSame('', $dsn->getPassword());
        $this->assertSame('host', $dsn->getHost());
        $this->assertSame(22, $dsn->getPort());
        $this->assertSame('ssh://user@host', $dsn->__toString());
    }

    public function testSshWithSchema(): void
    {
        $dsn = new SshUrl('ssh://user@host');
        $this->assertSame('user', $dsn->getUser());
        $this->assertSame('', $dsn->getPassword());
        $this->assertSame('host', $dsn->getHost());
        $this->assertSame(22, $dsn->getPort());
        $this->assertSame('ssh://user@host', $dsn->__toString());
    }

    public function testSshWithSchemaPort2206(): void
    {
        $dsn = new SshUrl('ssh://user@host:2206');
        $this->assertSame('user', $dsn->getUser());
        $this->assertSame('', $dsn->getPassword());
        $this->assertSame('host', $dsn->getHost());
        $this->assertSame(2206, $dsn->getPort());
        $this->assertSame('ssh://user@host:2206', $dsn->__toString());
    }

    public function testSshWithPassword(): void
    {
        $dsn = new SshUrl('ssh://user:password@host');
        $this->assertSame('user', $dsn->getUser());
        $this->assertSame('password', $dsn->getPassword());
        $this->assertSame('host', $dsn->getHost());
        $this->assertSame(22, $dsn->getPort());
        $this->assertSame('ssh://user:password@host', $dsn->__toString());
    }

    public function testSshWithIp(): void
    {
        $dsn = new SshUrl('user@192.168.0.1');
        $this->assertSame('user', $dsn->getUser());
        $this->assertSame('', $dsn->getPassword());
        $this->assertSame('192.168.0.1', $dsn->getHost());
        $this->assertSame(22, $dsn->getPort());
        $this->assertSame('ssh://user@192.168.0.1', $dsn->__toString());
    }
}
