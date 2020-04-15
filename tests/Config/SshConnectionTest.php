<?php

declare(strict_types=1);

namespace PHPSu\Tests\Config;

use InvalidArgumentException;
use PHPSu\Config\SshConnection;
use PHPUnit\Framework\TestCase;

class SshConnectionTest extends TestCase
{
    public function testSetInvalidHost(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $sshConnection = new SshConnection();
        $sshConnection->setHost('test/host');
    }
}
