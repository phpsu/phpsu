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
}
