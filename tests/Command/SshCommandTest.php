<?php
declare(strict_types=1);

namespace PHPSu\Tests\Command;

use PHPSu\Command\SshCommand;
use PHPSu\Config\SshConfig;
use PHPUnit\Framework\TestCase;

final class SshCommandTest extends TestCase
{
    public function testGenerate()
    {
        $sshConfig = new SshConfig();
        $sshConfig->setFile(new \SplTempFileObject());
        $ssh = new SshCommand();
        $ssh->setSshConfig($sshConfig)
            ->setInto('hosta');
        $this->assertSame('ssh -F php://temp hosta', $ssh->generate());
    }
}
