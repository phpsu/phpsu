<?php
declare(strict_types=1);

namespace PHPSu\Tests\Command;

use PHPSu\Command\RsyncCommand;
use PHPSu\Config\AppInstance;
use PHPSu\Config\FileSystem;
use PHPSu\Config\SshConfig;
use PHPUnit\Framework\TestCase;

final class RsyncCommandTest extends TestCase
{

    public function testRsyncWithAppInstance(): void
    {
        $sshConfig = new SshConfig();
        $sshConfig->setFile(new \SplTempFileObject());

        $instanceA = new AppInstance();
        $instanceA->setName('prod')
            ->setHost('hosta')
            ->setPath('/var/www/prod');

        $instanceB = new  AppInstance();
        $instanceB->setName('testing')
            ->setHost('hostc')
            ->setPath('/var/www/testing');

        $fileSystem = (new FileSystem())->setName('app')->setPath('');
        $generated = RsyncCommand::fromAppInstances($instanceA, $instanceB, $fileSystem, $fileSystem, 'local')->setSshConfig($sshConfig)->generate();
        $this->assertSame("rsync -avz -e 'ssh -F '\''php://temp'\''' 'hosta:/var/www/prod/*' 'hostc:/var/www/testing/'", $generated);
    }

    public function testGenerate(): void
    {
        $sshConfig = new SshConfig();
        $sshConfig->setFile(new \SplTempFileObject());
        $rsync = new RsyncCommand();
        $rsync->setSshConfig($sshConfig)
            ->setOptions('-r')
            ->setFromHost('hosta')
            ->setFromPath('~/test/*')
            ->setToPath('./__test/');

        $this->assertSame("rsync -r -e 'ssh -F '\''php://temp'\''' 'hosta:~/test/*' './__test/'", $rsync->generate());
    }

    public function testRsyncWithAppInstanceLocal(): void
    {
        $sshConfig = new SshConfig();
        $sshConfig->setFile(new \SplTempFileObject());

        $instanceA = new AppInstance();
        $instanceA->setName('prod')
            ->setHost('hosta')
            ->setPath('/var/www/prod');

        $instanceB = new AppInstance();
        $instanceB->setName('local');

        $fileSystem = (new FileSystem())->setName('app')->setPath('');
        $generated = RsyncCommand::fromAppInstances($instanceA, $instanceB, $fileSystem, $fileSystem, 'local')->setSshConfig($sshConfig)->generate();
        $this->assertSame("rsync -avz -e 'ssh -F '\''php://temp'\''' 'hosta:/var/www/prod/*' './'", $generated);
    }

    public function testLocalAndVarStorage(): void
    {
        $sshConfig = new SshConfig();
        $sshConfig->setFile(new \SplTempFileObject());

        $instanceA = new AppInstance();
        $instanceA->setName('prod')
            ->setHost('hosta')
            ->setPath('/var/www/prod');

        $instanceB = new AppInstance();
        $instanceB->setName('local');

        $fileSystem = (new FileSystem())->setName('app')->setPath('var/storage');
        $generated = RsyncCommand::fromAppInstances($instanceA, $instanceB, $fileSystem, $fileSystem, 'local')->setSshConfig($sshConfig)->generate();
        $this->assertSame("rsync -avz -e 'ssh -F '\''php://temp'\''' 'hosta:/var/www/prod/var/storage/*' './var/storage/'", $generated);
    }
}
