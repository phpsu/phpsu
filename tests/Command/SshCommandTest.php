<?php

declare(strict_types=1);

namespace PHPSu\Tests\Command;

use PHPSu\Command\SshCommand;
use PHPSu\Config\GlobalConfig;
use PHPSu\Config\SshConfig;
use PHPSu\ShellCommandBuilder\ShellBuilder;
use PHPUnit\Framework\TestCase;
use SplTempFileObject;
use Symfony\Component\Console\Output\OutputInterface;

final class SshCommandTest extends TestCase
{
    public function testSshCommandGenerate(): void
    {
        $sshConfig = new SshConfig();
        $sshConfig->setFile(new SplTempFileObject());
        $ssh = new SshCommand();
        $ssh->setSshConfig($sshConfig)
            ->setInto('hosta');
        $this->assertSame("ssh -F 'php://temp' 'hosta'", (string)$ssh->generate(ShellBuilder::new()));
    }

    public function testSshCommandQuiet(): void
    {
        $sshConfig = new SshConfig();
        $sshConfig->setFile(new SplTempFileObject());
        $ssh = new SshCommand();
        $ssh->setSshConfig($sshConfig)
            ->setInto('hosta')
            ->setVerbosity(OutputInterface::VERBOSITY_QUIET);
        $this->assertSame("ssh -q -F 'php://temp' 'hosta'", (string)$ssh->generate(ShellBuilder::new()));
    }

    public function testSshCommandVerbose(): void
    {
        $sshConfig = new SshConfig();
        $sshConfig->setFile(new SplTempFileObject());
        $ssh = new SshCommand();
        $ssh->setSshConfig($sshConfig)
            ->setInto('hosta')
            ->setVerbosity(OutputInterface::VERBOSITY_VERBOSE);
        $this->assertSame("ssh -v -F 'php://temp' 'hosta'", (string)$ssh->generate(ShellBuilder::new()));
    }

    public function testSshCommandVeryVerbose(): void
    {
        $sshConfig = new SshConfig();
        $sshConfig->setFile(new SplTempFileObject());
        $ssh = new SshCommand();
        $ssh->setSshConfig($sshConfig)
            ->setInto('hosta')
            ->setVerbosity(OutputInterface::VERBOSITY_VERY_VERBOSE);
        $this->assertSame("ssh -vv -F 'php://temp' 'hosta'", (string)$ssh->generate(ShellBuilder::new()));
    }

    public function testSshCommandDebug(): void
    {
        $sshConfig = new SshConfig();
        $sshConfig->setFile(new SplTempFileObject());
        $ssh = new SshCommand();
        $ssh->setSshConfig($sshConfig)
            ->setInto('hosta')
            ->setVerbosity(OutputInterface::VERBOSITY_DEBUG);
        $this->assertSame("ssh -vvv -F 'php://temp' 'hosta'", (string)$ssh->generate(ShellBuilder::new()));
    }

    public function testSshCommandGetter(): void
    {
        $sshConfig = new SshConfig();
        $sshConfig->setFile(new SplTempFileObject());
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

    public function testSameException(): void
    {
        $sshConfig = new SshConfig();
        $sshConfig->setFile(new SplTempFileObject());
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Running a command locally requires a command');
        $command = SshCommand::fromGlobal(new GlobalConfig(), 'same', 'same', OutputInterface::VERBOSITY_NORMAL);
        $command->generate(new ShellBuilder());
    }

    public function testLocalCommand(): void
    {
        $sshConfig = new SshConfig();
        $sshConfig->setFile(new SplTempFileObject());
        $command = SshCommand::fromGlobal(new GlobalConfig(), 'same', 'same', OutputInterface::VERBOSITY_NORMAL);
        $command->setCommand(ShellBuilder::command('echo'));
        $result = $command->generate(new ShellBuilder());
        self::assertSame('echo', (string)$result);
    }
}
