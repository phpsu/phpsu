<?php

declare(strict_types=1);

namespace PHPSu\Tests\Command;

use PHPSu\Command\SshCommand;
use PHPSu\Config\GlobalConfig;
use PHPSu\Config\SshConfig;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\OutputInterface;

final class SshCommandTest extends TestCase
{
    public function testSshCommandGenerate()
    {
        $sshConfig = new SshConfig();
        $sshConfig->setFile(new \SplTempFileObject());
        $ssh = new SshCommand();
        $ssh->setSshConfig($sshConfig)
            ->setInto('hosta');
        $this->assertSame("ssh -F 'php://temp' 'hosta'", $ssh->generate());
    }

    public function testSshCommandQuiet()
    {
        $sshConfig = new SshConfig();
        $sshConfig->setFile(new \SplTempFileObject());
        $ssh = new SshCommand();
        $ssh->setSshConfig($sshConfig)
            ->setInto('hosta')
            ->setVerbosity(OutputInterface::VERBOSITY_QUIET);
        $this->assertSame("ssh -q -F 'php://temp' 'hosta'", $ssh->generate());
    }

    public function testSshCommandVerbose()
    {
        $sshConfig = new SshConfig();
        $sshConfig->setFile(new \SplTempFileObject());
        $ssh = new SshCommand();
        $ssh->setSshConfig($sshConfig)
            ->setInto('hosta')
            ->setVerbosity(OutputInterface::VERBOSITY_VERBOSE);
        $this->assertSame("ssh -v -F 'php://temp' 'hosta'", $ssh->generate());
    }

    public function testSshCommandVeryVerbose()
    {
        $sshConfig = new SshConfig();
        $sshConfig->setFile(new \SplTempFileObject());
        $ssh = new SshCommand();
        $ssh->setSshConfig($sshConfig)
            ->setInto('hosta')
            ->setVerbosity(OutputInterface::VERBOSITY_VERY_VERBOSE);
        $this->assertSame("ssh -vv -F 'php://temp' 'hosta'", $ssh->generate());
    }

    public function testSshCommandDebug()
    {
        $sshConfig = new SshConfig();
        $sshConfig->setFile(new \SplTempFileObject());
        $ssh = new SshCommand();
        $ssh->setSshConfig($sshConfig)
            ->setInto('hosta')
            ->setVerbosity(OutputInterface::VERBOSITY_DEBUG);
        $this->assertSame("ssh -vvv -F 'php://temp' 'hosta'", $ssh->generate());
    }

    public function testSshCommandGetter()
    {
        $sshConfig = new SshConfig();
        $sshConfig->setFile(new \SplTempFileObject());
        $ssh = new SshCommand();
        $ssh->setSshConfig($sshConfig)
            ->setInto('hosta')
            ->setPath('/path/g2v7b89')
            ->setVerbosity(OutputInterface::VERBOSITY_DEBUG);
        $this->assertSame('hosta', $ssh->getInto());
        $this->assertSame('/path/g2v7b89', $ssh->getPath());
        $this->assertSame($sshConfig, $ssh->getSshConfig());
        $this->assertSame(OutputInterface::VERBOSITY_DEBUG, $ssh->getVerbosity());
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage  the found host and the current Host are the same: same
     */
    public function testSameException()
    {
        $sshConfig = new SshConfig();
        $sshConfig->setFile(new \SplTempFileObject());
        $this->expectExceptionMessage('the found host and the current Host are the same: same');
        SshCommand::fromGlobal(new GlobalConfig(), 'same', 'same', OutputInterface::VERBOSITY_NORMAL);
    }
}
