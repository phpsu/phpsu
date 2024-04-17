<?php

declare(strict_types=1);

namespace PHPSu\Tests\Config;

use PHPSu\Command\DatabaseCommand;
use PHPSu\Command\RsyncCommand;
use PHPSu\Command\SshCommand;
use PHPSu\Config\AppInstance;
use PHPSu\Config\Database;
use PHPSu\Config\DatabaseConnectionDetails;
use PHPSu\Config\FileSystem;
use PHPSu\Config\GlobalConfig;
use PHPSu\Config\SshConfig;
use PHPSu\Config\SshConfigHost;
use PHPSu\Config\SshConnection;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\OutputInterface;

final class GlobalConfigTest extends TestCase
{
    public function testSshFromGlobalConfig(): void
    {
        $global = self::getGlobalConfig();

        $sshCommand = SshCommand::fromGlobal($global, 'production', 'local', OutputInterface::VERBOSITY_NORMAL);
        $this->assertEquals((new SshCommand())->setInto('serverEu')->setPath('/var/www/production'), $sshCommand);

        $sshCommand = SshCommand::fromGlobal($global, 'testing', 'local', OutputInterface::VERBOSITY_NORMAL);
        $this->assertEquals((new SshCommand())->setInto('serverEu')->setPath('/var/www/testing'), $sshCommand);

        $sshCommand = SshCommand::fromGlobal($global, 'serverEu', 'local', OutputInterface::VERBOSITY_QUIET);
        $this->assertEquals((new SshCommand())->setInto('serverEu')->setVerbosity(OutputInterface::VERBOSITY_QUIET), $sshCommand);
    }

    public function testDatabaseFromGlobalConfig(): void
    {
        $global = self::getGlobalConfig();

        $rsyncCommands = DatabaseCommand::fromGlobal($global, 'production', 'testing', 'local', false, OutputInterface::VERBOSITY_NORMAL);
        $connectionDetails = DatabaseConnectionDetails::fromUrlString('mysql://user:pw@host:3307/database');
        $db = (new Database())->setConnectionDetails($connectionDetails)->setName('app');
        $this->assertEquals([
            (new DatabaseCommand())
                ->setName('database:app')
                ->setFromHost('serverEu')
                ->setFromDatabase($db)
                ->setToHost('serverEu')
                ->setToDatabase($db),
        ], $rsyncCommands);
    }

    public function testRsyncFromGlobalConfig(): void
    {
        $global = self::getGlobalConfig();

        $rsyncCommands = RsyncCommand::fromGlobal($global, 'production', 'testing', 'local', false, OutputInterface::VERBOSITY_NORMAL);
        $this->assertEquals([
            (new RsyncCommand())->setName('filesystem:fileadmin')->setSourceHost('serverEu')->setSourcePath('/var/www/production/fileadmin/')->setDestinationHost('serverEu')->setToPath('/var/www/testing/fileadmin/'),
            (new RsyncCommand())->setName('filesystem:uploads')->setSourceHost('serverEu')->setSourcePath('/var/www/production/uploads/')->setDestinationHost('serverEu')->setToPath('/var/www/testing/uploads/'),
        ], $rsyncCommands);
    }

    public function testSshConfigFromGlobalConfig(): void
    {
        $global = self::getGlobalConfig();

        $sshConfig = SshConfig::fromGlobal($global, 'local');
        $sshConfigExpected = new SshConfig();
        $sshConfigExpected->serverEu = new SshConfigHost();
        $sshConfigExpected->serverEu->User = 'user';
        $sshConfigExpected->serverEu->HostName = 'server.eu';
        $sshConfigExpected->{'*'} = new SshConfigHost();
        $sshConfigExpected->{'*'}->ForwardAgent = 'yes';
        $sshConfigExpected->{'*'}->ServerAliveInterval = '120';
        $this->assertEquals($sshConfigExpected, $sshConfig);
    }

    public function testOverwriteDefaultSshConfig(): void
    {
        $global = self::getGlobalConfig();
        $global->setDefaultSshConfig(['ForwardAgent' => 'no']);

        $sshConfig = SshConfig::fromGlobal($global, 'local');
        $sshConfigExpected = new SshConfig();
        $this->assertFalse(isset($sshConfigExpected->serverEu));
        $sshConfigExpected->serverEu = new SshConfigHost();
        $this->assertTrue(isset($sshConfigExpected->serverEu));
        $sshConfigExpected->serverEu->User = 'user';
        $sshConfigExpected->serverEu->HostName = 'server.eu';
        $sshConfigExpected->{'*'} = new SshConfigHost();
        $sshConfigExpected->{'*'}->ForwardAgent = 'no';
        $this->assertEquals($sshConfigExpected, $sshConfig);
    }

    public function testAddSshConnectionAndChangeFrom(): void
    {
        $global = self::getGlobalConfig();
        $global->addSshConnection('host42', 'ssh://user@localhost')->setFrom(['serverEu']);
        $sshConnections = $global->getSshConnections();
        $sshConnections->compile();

        $result = $sshConnections->getPossibilities('host42');
        $this->assertArrayHasKey('serverEu', $result);
    }

    public static function getGlobalConfig(): GlobalConfig
    {
        $global = new GlobalConfig();
        $global->addFilesystem('fileadmin', 'fileadmin');
        $global->addFilesystemObject((new FileSystem())->setName('uploads')->setPath('uploads'));
        $global->addDatabaseByUrl('app', 'mysql://user:pw@host:3307/database');
        $global->addSshConnectionObject((new SshConnection())->setHost('serverEu')->setUrl('user@server.eu'));
        $global->addAppInstanceObject((new AppInstance())->setName('production')->setHost('serverEu')->setPath('/var/www/production'));
        $global->addAppInstanceObject((new AppInstance())->setName('testing')->setHost('serverEu')->setPath('/var/www/testing'));

        $cwd = getcwd();
        assert(is_string($cwd));
        $global->addAppInstance('local', 'local', $cwd);
        return $global;
    }
}
