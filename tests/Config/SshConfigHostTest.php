<?php

declare(strict_types=1);

namespace PHPSu\Tests\Config;

use PHPSu\Config\SshConfigHost;
use PHPUnit\Framework\TestCase;

final class SshConfigHostTest extends TestCase
{
    public function testSetIssetGet(): void
    {
        $sshConfigHost = new SshConfigHost();
        $this->assertFalse(isset($sshConfigHost->User));
        $sshConfigHost->User = 'Test';
        $this->assertTrue(isset($sshConfigHost->User));
        $this->assertSame('Test', $sshConfigHost->User);
    }
}
