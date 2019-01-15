<?php
declare(strict_types=1);

namespace PHPSu\Tests\Config;

use PHPSu\Config\SshConnection;
use PHPSu\Config\SshConnections;
use PHPUnit\Framework\TestCase;

final class SshConnectionsTest extends TestCase
{
    /**
     * @expectedException \Exception
     * @expectedExceptionMessage suspicious Connection Model found: fromHere->test has more than one definition
     */
    public function testAdd(): void
    {
        $sshConnections = new SshConnections();
        $sshConnections->add((new SshConnection())->setHost('test')->setFrom(['fromHere']));
        $sshConnections->add((new SshConnection())->setHost('test')->setFrom(['fromHere']));
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Host test not found in SshConnections
     */
    public function testGetPossibilities(): void
    {
        $sshConnections = new SshConnections();
        $sshConnections->getPossibilities('test');
    }
}
