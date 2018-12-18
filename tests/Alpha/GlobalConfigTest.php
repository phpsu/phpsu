<?php
declare(strict_types=1);

namespace PHPSu\Tests\Alpha;

use PHPSu\Alpha\AppInstance;
use PHPSu\Alpha\GlobalConfig;
use PHPSu\Alpha\RsyncCommand;
use PHPSu\Alpha\SshCommand;
use PHPSu\Alpha\SshConfig;
use PHPSu\Alpha\SshConfigHost;
use PHPSu\Alpha\SshConnection;
use PHPSu\Alpha\SshConnections;
use PHPUnit\Framework\TestCase;

final class GlobalConfigTest extends TestCase
{
    public function testSshFromGlobalConfig(): void
    {
        $global = static::getGlobalConfig();

        $sshCommand = SshCommand::fromGlobal($global, 'production', 'local');
        $this->assertEquals((new SshCommand())->setInto('serverEu'), $sshCommand);

        $sshCommand = SshCommand::fromGlobal($global, 'testing', 'local');
        $this->assertEquals((new SshCommand())->setInto('serverEu'), $sshCommand);

        $sshCommand = SshCommand::fromGlobal($global, 'serverEu', 'local');
        $this->assertEquals((new SshCommand())->setInto('serverEu'), $sshCommand);
    }

    public function testRsyncFromGlobalConfig(): void
    {
        $global = static::getGlobalConfig();

        $rsyncCommands = RsyncCommand::fromGlobal($global, 'production', 'testing', 'local');
        $this->assertEquals([
            (new RsyncCommand())->setFrom('serverEu:/var/www/production/fileadmin/*')->setTo('serverEu:/var/www/testing/fileadmin/'),
            (new RsyncCommand())->setFrom('serverEu:/var/www/production/uploads/*')->setTo('serverEu:/var/www/testing/uploads/'),
        ], $rsyncCommands);
    }

    public function testSshConfigFromGlobalConfig(): void
    {
        $global = static::getGlobalConfig();

        $sshConfig = SshConfig::fromGlobal($global, 'local');
        $sshConfigExpected = new SshConfig();
        $sshConfigExpected->serverEu = new SshConfigHost();
        $sshConfigExpected->serverEu->User = 'user';
        $sshConfigExpected->serverEu->HostName = 'server.eu';
        $this->assertEquals($sshConfigExpected, $sshConfig);
    }

    public static function getGlobalConfig(): GlobalConfig
    {
        $global = new GlobalConfig();
        $global->fileSystems = new \stdClass();
        $global->fileSystems->fileadmin = 'fileadmin';
        $global->fileSystems->uploads = 'uploads';
        $global->sshConnections = new SshConnections();
        $global->sshConnections->addConnection((new SshConnection())->setHost('serverEu')->setUrl('user@server.eu')->setIdentityFile('docker/testCaseD/id_rsa'));
        $global->appInstances = new \stdClass();
        $global->appInstances->production = (new AppInstance())->setName('production')->setHost('serverEu')->setPath('/var/www/production');
        $global->appInstances->testing = (new AppInstance())->setName('testing')->setHost('serverEu')->setPath('/var/www/testing');
        $global->appInstances->local = (new AppInstance())->setName('local')->setHost('local')->setPath(getcwd());
        return $global;
    }
}
