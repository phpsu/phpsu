<?php
declare(strict_types=1);

namespace PHPSu\Tests\Alpha;

use PHPSu\Alpha\AppInstance;
use PHPSu\Alpha\Database;
use PHPSu\Alpha\DatabaseCommand;
use PHPSu\Alpha\FileSystem;
use PHPSu\Alpha\GlobalConfig;
use PHPSu\Alpha\RsyncCommand;
use PHPSu\Alpha\SshCommand;
use PHPSu\Alpha\SshConfig;
use PHPSu\Alpha\SshConfigHost;
use PHPSu\Alpha\SshConnection;
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

    public function testDatabaseFromGlobalConfig(): void
    {
        $global = static::getGlobalConfig();

        $rsyncCommands = DatabaseCommand::fromGlobal($global, 'production', 'testing', 'local');
        $this->assertEquals([
            (new DatabaseCommand())->setName('database:app')->setFromHost('serverEu')->setFromUrl('mysql://user:pw@host:3307/database')->setToHost('serverEu')->setToUrl('mysql://user:pw@host:3307/database'),
        ], $rsyncCommands);
    }

    public function testRsyncFromGlobalConfig(): void
    {
        $global = static::getGlobalConfig();

        $rsyncCommands = RsyncCommand::fromGlobal($global, 'production', 'testing', 'local');
        $this->assertEquals([
            (new RsyncCommand())->setName('filesystem:fileadmin')->setFromHost('serverEu')->setFromPath('/var/www/production/fileadmin/*')->setToHost('serverEu')->setToPath('/var/www/testing/fileadmin/'),
            (new RsyncCommand())->setName('filesystem:uploads')->setFromHost('serverEu')->setFromPath('/var/www/production/uploads/*')->setToHost('serverEu')->setToPath('/var/www/testing/uploads/'),
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
        $sshConfigExpected->serverEu->IdentityFile = 'docker/testCaseD/id_rsa';
        $this->assertEquals($sshConfigExpected, $sshConfig);
    }

    public static function getGlobalConfig(): GlobalConfig
    {
        $global = new GlobalConfig();
        $global->addFilesystem((new FileSystem())->setName('fileadmin')->setPath('fileadmin'));
        $global->addFilesystem((new FileSystem())->setName('uploads')->setPath('uploads'));
        $global->addDatabase((new Database())->setName('app')->setUrl('mysql://user:pw@host:3307/database'));
        $global->addSshConnection((new SshConnection())->setHost('serverEu')->setUrl('user@server.eu')->setIdentityFile('docker/testCaseD/id_rsa'));
        $global->addAppInstance((new AppInstance())->setName('production')->setHost('serverEu')->setPath('/var/www/production'));
        $global->addAppInstance((new AppInstance())->setName('testing')->setHost('serverEu')->setPath('/var/www/testing'));
        $global->addAppInstance((new AppInstance())->setName('local')->setHost('local')->setPath(getcwd()));
        return $global;
    }
}
