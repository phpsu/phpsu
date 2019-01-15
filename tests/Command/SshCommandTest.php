<?php
declare(strict_types=1);

namespace PHPSu\Tests\Command;

use PHPSu\Command\SshCommand;
use PHPSu\Config\GlobalConfig;
use PHPSu\Config\SshConfig;
use PHPUnit\Framework\TestCase;

final class SshCommandTest extends TestCase
{
    public function testGenerate(): void
    {
        $sshConfig = new SshConfig();
        $sshConfig->setFile(new \SplTempFileObject());
        $ssh = new SshCommand();
        $ssh->setSshConfig($sshConfig)
            ->setInto('hosta');
        $this->assertSame('ssh -F php://temp hosta', $ssh->generate());
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage  the found host and the current Host are the same: same
     */
    public function testSameException(): void
    {
        $sshConfig = new SshConfig();
        $sshConfig->setFile(new \SplTempFileObject());
        SshCommand::fromGlobal(new GlobalConfig(), 'same', 'same');
    }
}
