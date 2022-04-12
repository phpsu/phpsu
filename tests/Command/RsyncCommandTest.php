<?php

declare(strict_types=1);

namespace PHPSu\Tests\Command;

use PHPSu\Command\RsyncCommand;
use PHPSu\Config\AppInstance;
use PHPSu\Config\FileSystem;
use PHPSu\Config\SshConfig;
use PHPSu\ShellCommandBuilder\ShellBuilder;
use PHPUnit\Framework\TestCase;
use SplTempFileObject;
use Symfony\Component\Console\Output\OutputInterface;

final class RsyncCommandTest extends TestCase
{
    public function testRsyncWithAppInstance(): void
    {
        $sshConfig = new SshConfig();
        $sshConfig->setFile(new SplTempFileObject());

        $instanceA = new AppInstance();
        $instanceA->setName('prod')
            ->setHost('hosta')
            ->setPath('/var/www/prod');

        $instanceB = new  AppInstance();
        $instanceB->setName('testing')
            ->setHost('hostc')
            ->setPath('/var/www/testing');

        $fileSystem = (new FileSystem())->setName('app')->setPath('');
        $generated = RsyncCommand::fromAppInstances($instanceA, $instanceB, $fileSystem, $fileSystem, 'local', false, OutputInterface::VERBOSITY_NORMAL)->setSshConfig($sshConfig)->generate(ShellBuilder::new());
        $this->assertSame("rsync -az -e 'ssh -F '\''php://temp'\''' 'hosta:/var/www/prod/' 'hostc:/var/www/testing/'", (string)$generated);
    }

    public function testGenerate(): void
    {
        $sshConfig = new SshConfig();
        $sshConfig->setFile(new SplTempFileObject());
        $rsync = new RsyncCommand();
        $rsync->setSshConfig($sshConfig)
            ->setOptions('-r')
            ->setSourceHost('hosta')
            ->setSourcePath('~/test/*')
            ->setToPath('./__test/');

        $this->assertSame("rsync -r -e 'ssh -F '\''php://temp'\''' 'hosta:~/test/*' './__test/'", (string)$rsync->generate(ShellBuilder::new()));
    }

    public function testGenerateMultipleOptions(): void
    {
        $sshConfig = new SshConfig();
        $sshConfig->setFile(new SplTempFileObject());
        $rsync = new RsyncCommand();
        $rsync->setSshConfig($sshConfig)
            ->setOptions('-r --colorize')
            ->setSourceHost('hosta')
            ->setSourcePath('~/test/*')
            ->setToPath('./__test/');

        $this->assertSame("rsync -r --colorize -e 'ssh -F '\''php://temp'\''' 'hosta:~/test/*' './__test/'", (string)$rsync->generate(ShellBuilder::new()));
    }

    public function testRsyncWithAppInstanceLocal(): void
    {
        $sshConfig = new SshConfig();
        $sshConfig->setFile(new SplTempFileObject());

        $instanceA = new AppInstance();
        $instanceA->setName('prod')
            ->setHost('hosta')
            ->setPath('/var/www/prod');

        $instanceB = new AppInstance();
        $instanceB->setName('local');

        $fileSystem = (new FileSystem())->setName('app')->setPath('');
        $generated = RsyncCommand::fromAppInstances($instanceA, $instanceB, $fileSystem, $fileSystem, 'local', false, OutputInterface::VERBOSITY_NORMAL)->setSshConfig($sshConfig)->generate(ShellBuilder::new());
        $this->assertSame("rsync -az -e 'ssh -F '\''php://temp'\''' 'hosta:/var/www/prod/' './'", (string)$generated);
    }

    public function testLocalAndVarStorage(): void
    {
        $sshConfig = new SshConfig();
        $sshConfig->setFile(new SplTempFileObject());

        $instanceA = new AppInstance();
        $instanceA->setName('prod')
            ->setHost('hosta')
            ->setPath('/var/www/prod');

        $instanceB = new AppInstance();
        $instanceB->setName('local');

        $fileSystem = (new FileSystem())->setName('app')->setPath('var/storage');
        $generated = RsyncCommand::fromAppInstances($instanceA, $instanceB, $fileSystem, $fileSystem, 'local', false, OutputInterface::VERBOSITY_NORMAL)->setSshConfig($sshConfig)->generate(ShellBuilder::new());
        $this->assertSame("rsync -az -e 'ssh -F '\''php://temp'\''' 'hosta:/var/www/prod/var/storage/' './var/storage/'", (string)$generated);
    }

    public function testRsyncQuiet(): void
    {
        $sshConfig = new SshConfig();
        $sshConfig->setFile(new SplTempFileObject());

        $instanceA = new AppInstance();
        $instanceA->setName('prod')
            ->setHost('hosta')
            ->setPath('/var/www/prod');

        $instanceB = new AppInstance();
        $instanceB->setName('local');

        $fileSystem = (new FileSystem())->setName('app')->setPath('var/storage');
        $generated = RsyncCommand::fromAppInstances($instanceA, $instanceB, $fileSystem, $fileSystem, 'local', false, OutputInterface::VERBOSITY_QUIET)->setSshConfig($sshConfig)->generate(ShellBuilder::new());
        $this->assertSame("rsync -q -az -e 'ssh -F '\''php://temp'\''' 'hosta:/var/www/prod/var/storage/' './var/storage/'", (string)$generated);
    }

    public function testRsyncVerbose(): void
    {
        $sshConfig = new SshConfig();
        $sshConfig->setFile(new SplTempFileObject());

        $instanceA = new AppInstance();
        $instanceA->setName('prod')
            ->setHost('hosta')
            ->setPath('/var/www/prod');

        $instanceB = new AppInstance();
        $instanceB->setName('local');

        $fileSystem = (new FileSystem())->setName('app')->setPath('var/storage');
        $generated = RsyncCommand::fromAppInstances($instanceA, $instanceB, $fileSystem, $fileSystem, 'local', false, OutputInterface::VERBOSITY_VERBOSE)->setSshConfig($sshConfig)->generate(ShellBuilder::new());
        $this->assertSame("rsync -v -az -e 'ssh -F '\''php://temp'\''' 'hosta:/var/www/prod/var/storage/' './var/storage/'", (string)$generated);
    }

    public function testRsyncVeryVerbose(): void
    {
        $sshConfig = new SshConfig();
        $sshConfig->setFile(new SplTempFileObject());

        $instanceA = new AppInstance();
        $instanceA->setName('prod')
            ->setHost('hosta')
            ->setPath('/var/www/prod');

        $instanceB = new AppInstance();
        $instanceB->setName('local');

        $fileSystem = (new FileSystem())->setName('app')->setPath('var/storage');
        $generated = RsyncCommand::fromAppInstances($instanceA, $instanceB, $fileSystem, $fileSystem, 'local', false, OutputInterface::VERBOSITY_VERY_VERBOSE)->setSshConfig($sshConfig)->generate(ShellBuilder::new());
        $this->assertSame("rsync -vv -az -e 'ssh -F '\''php://temp'\''' 'hosta:/var/www/prod/var/storage/' './var/storage/'", (string)$generated);
    }

    public function testRsyncDebug(): void
    {
        $sshConfig = new SshConfig();
        $sshConfig->setFile(new SplTempFileObject());

        $instanceA = new AppInstance();
        $instanceA->setName('prod')
            ->setHost('hosta')
            ->setPath('/var/www/prod');

        $instanceB = new AppInstance();
        $instanceB->setName('local');

        $fileSystem = (new FileSystem())->setName('app')->setPath('var/storage');
        $generated = RsyncCommand::fromAppInstances($instanceA, $instanceB, $fileSystem, $fileSystem, 'local', false, OutputInterface::VERBOSITY_DEBUG)->setSshConfig($sshConfig)->generate(ShellBuilder::new());
        $this->assertSame("rsync -vvv -az -e 'ssh -F '\''php://temp'\''' 'hosta:/var/www/prod/var/storage/' './var/storage/'", (string)$generated);
    }

    public function testRsyncCommandGetter(): void
    {
        $sshConfig = new SshConfig();
        $sshConfig->setFile(new SplTempFileObject());
        $rsync = new RsyncCommand();
        $rsync->setName('rsyncName')
            ->setSshConfig($sshConfig)
            ->setOptions('-r')
            ->setSourceHost('hosta')
            ->setSourcePath('~/test/*')
            ->setDestinationHost('hostc')
            ->setToPath('./__test/');

        $this->assertSame('rsyncName', $rsync->getName());
        $this->assertSame($sshConfig, $rsync->getSshConfig());
        $this->assertSame('r', $rsync->getOptions());
        $this->assertSame('hosta', $rsync->getSourceHost());
        $this->assertSame('~/test/*', $rsync->getSourcePath());
        $this->assertSame('hostc', $rsync->getDestinationHost());
        $this->assertSame('./__test/', $rsync->getToPath());
    }
}
