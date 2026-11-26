<?php

declare(strict_types=1);

namespace PHPSu\Tests\Config;

use Exception;
use PHPSu\Config\SshConnection;
use PHPSu\Config\SshConnections;
use PHPUnit\Framework\TestCase;

final class SshConnectionsTest extends TestCase
{
    public function testAdd(): void
    {
        $this->expectExceptionMessage("suspicious Connection Model found: fromHere->test has more than one definition");
        $this->expectException(Exception::class);
        $sshConnections = new SshConnections();
        $sshConnections->add((new SshConnection())->setHost('test')->setFrom(['fromHere']));
        $sshConnections->add((new SshConnection())->setHost('test')->setFrom(['fromHere']));

        $this->expectExceptionMessage('suspicious Connection Model found: fromHere->test has more than one definition');
        $sshConnections->compile();
    }

    public function testGetPossibilities(): void
    {
        $this->expectExceptionMessage("Host test not found in SshConnections");
        $this->expectException(Exception::class);
        $sshConnections = new SshConnections();
        $this->expectExceptionMessage('Host test not found in SshConnections');
        $sshConnections->getPossibilities('test');
    }

    public function testExternalHosts(): void
    {
        $sshConnections = new SshConnections();
        $sshConnections->add($connection = (new SshConnection())->setHost('hosta')->setUrl('user@host_a'));
        $sshConnections->addExternalHosts('hosta', 'hostb');

        $this->assertFalse($sshConnections->isExternalHost('hosta'), 'SshConnections take precedence');
        $this->assertTrue($sshConnections->isExternalHost('hostb'));
        $this->assertFalse($sshConnections->isExternalHost('hostc'));
        $this->assertSame(['' => $connection], $sshConnections->getPossibilities('hosta'));
        $this->assertSame([], $sshConnections->getPossibilities('hostb'));
        $this->assertSame(['hosta'], $sshConnections->getAllHosts());

        $this->expectExceptionMessage('Host hostc not found in SshConnections');
        $sshConnections->getPossibilities('hostc');
    }
}
