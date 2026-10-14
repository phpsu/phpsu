<?php

declare(strict_types=1);

namespace PHPSu\Tests\Config;

use InvalidArgumentException;
use PHPSu\Config\SshConfigFile;
use PHPUnit\Framework\TestCase;

final class SshConfigFileTest extends TestCase
{
    public function testDefaults(): void
    {
        $sshConfigFile = new SshConfigFile('ssh/config');
        $this->assertSame('ssh/config', $sshConfigFile->getSource());
        $this->assertSame(600, $sshConfigFile->getTtl());
        $this->assertFalse($sshConfigFile->isUrl());
        $this->assertFalse($sshConfigFile->isLoaded());
        $this->assertNull($sshConfigFile->getPath());
        $this->assertSame([], $sshConfigFile->getHosts());
        $this->assertNull($sshConfigFile->getUsername());
        $this->assertNull($sshConfigFile->getPassword());
    }

    public function testHttpsUrl(): void
    {
        $sshConfigFile = (new SshConfigFile('HTTPS://example.com/ssh_config'))
            ->setTtl(60)
            ->setBasicAuth('user', 'secret');
        $this->assertTrue($sshConfigFile->isUrl());
        $this->assertSame(60, $sshConfigFile->getTtl());
        $this->assertSame('user', $sshConfigFile->getUsername());
        $this->assertSame('secret', $sshConfigFile->getPassword());
    }

    public function testSetLoaded(): void
    {
        $sshConfigFile = (new SshConfigFile('/etc/ssh_config'))->setLoaded('/etc/ssh_config', ['hosta']);
        $this->assertTrue($sshConfigFile->isLoaded());
        $this->assertSame('/etc/ssh_config', $sshConfigFile->getPath());
        $this->assertSame(['hosta'], $sshConfigFile->getHosts());
    }

    public function testSchemeIsCaseInsensitive(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new SshConfigFile('HTTP://example.com/ssh_config');
    }

    public function testSchemeOnlyAtTheBeginning(): void
    {
        $sshConfigFile = new SshConfigFile('configs/http://example.com');
        $this->assertFalse($sshConfigFile->isUrl());
        $this->assertSame('other', $sshConfigFile->setSource('other')->getSource());
    }

    public function testHttpIsNotAllowed(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('ssh config file url http://example.com/ssh_config must use https');
        new SshConfigFile('http://example.com/ssh_config');
    }

    public function testOtherSchemeIsNotAllowed(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new SshConfigFile('ftp://example.com/ssh_config');
    }

    public function testEmptySourceIsNotAllowed(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('ssh config file source can not be empty');
        new SshConfigFile('');
    }

    public function testNegativeTtlIsNotAllowed(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('ttl must be positive, -1 given');
        (new SshConfigFile('ssh/config'))->setTtl(-1);
    }
}
