<?php

declare(strict_types=1);

namespace PHPSu\Tests\Command;

use Exception;
use GrumPHP\Task\Shell;
use PHPSu\Command\CommandGenerator;
use PHPSu\Config\AppInstance;
use PHPSu\Config\Compression\GzipCompression;
use PHPSu\Config\Database;
use PHPSu\Config\FileSystem;
use PHPSu\Config\GlobalConfig;
use PHPSu\Config\SshConnection;
use PHPSu\Options\SyncOptions;
use PHPSu\ShellCommandBuilder\ShellBuilder;
use PHPSu\Tests\ControllerTest;
use PHPUnit\Framework\TestCase;
use SplFileObject;
use SplTempFileObject;

final class CommandGeneratorTest extends TestCase
{
    private static function getGlobalConfig(): GlobalConfig
    {
        $globalConfig = new GlobalConfig();
        $globalConfig->addFilesystemObject((new FileSystem())->setName('fileadmin')->setPath('fileadmin'));
        $globalConfig->addFilesystemObject((new FileSystem())->setName('uploads')->setPath('uploads'));
        $globalConfig->addDatabaseObject((new Database())->setName('app')->setUrl('mysql://user:pw@host:3307/database')->setRemoveDefinerFromDump(false));
        $globalConfig->addSshConnection('serverEu', 'user@server.eu');
        $globalConfig->addSshConnectionObject((new SshConnection())->setHost('stagingServer')->setUrl('staging@stagingServer.server.eu'));
        $globalConfig->addAppInstanceObject((new AppInstance())->setName('production')->setHost('serverEu')->setPath('/var/www/production')->addFilesystemObject(
            (new FileSystem())->setName('fileadmin')->setPath('fileadmin2')
        )->addDatabaseObject(
            (new Database())->setName('app')->setUrl('mysql://root:root@appHost/appDatabase')->setRemoveDefinerFromDump(false)
        ));
        $globalConfig->addAppInstanceObject((new AppInstance())->setName('staging')->setHost('stagingServer')->setPath('/var/www/staging'));
        $globalConfig->addAppInstanceObject((new AppInstance())->setName('testing')->setHost('serverEu')->setPath('/var/www/testing'));
        $globalConfig->addAppInstanceObject((new AppInstance())->setName('local')->setHost('')->setPath('./'));
        $globalConfig->addAppInstanceObject((new AppInstance())->setName('local2')->setHost('')->setPath('../local2'));
        return $globalConfig;
    }

    public function testSshGeneration(): void
    {
        $globalConfig = static::getGlobalConfig();
        $commandGenerator = new CommandGenerator($globalConfig);
        $commandGenerator->setFile($file = new SplTempFileObject());
        $result = $commandGenerator->sshCommand('production', '', null);
        $file->rewind();
        $this->assertEquals('Host serverEu' . PHP_EOL, $file->getCurrentLine());
        $this->assertSame("ssh -F 'php://temp' 'serverEu' -t 'cd '\''/var/www/production'\'' ; bash --login'", (string)$result);
    }

    public function testSshWithCommand(): void
    {
        $globalConfig = static::getGlobalConfig();
        $commandGenerator = new CommandGenerator($globalConfig);
        $commandGenerator->setFile($file = new SplTempFileObject());
        $lsCommand = ShellBuilder::command('ls')->addShortOption('alh')->addOption('color');
        $result = $commandGenerator->sshCommand('production', '', $lsCommand);
        $this->assertSame("ssh -F 'php://temp' 'serverEu' -t 'cd '\''/var/www/production'\'' ; ls -alh --color'", (string)$result);
        $result = $commandGenerator->sshCommand('production', '', ShellBuilder::command('echo')->addArgument('test'));
        $this->assertSame("ssh -F 'php://temp' 'serverEu' -t 'cd '\''/var/www/production'\'' ; echo '\''test'\'''", (string)$result);
    }

    public function testMysqlCommandGenerationForProduction(): void
    {
        $globalConfig = static::getGlobalConfig();
        $commandGenerator = new CommandGenerator($globalConfig);
        $commandGenerator->setFile($file = new SplTempFileObject());
        $mysqlCommand = $commandGenerator->mysqlCommand('production', null, null);
        $file->rewind();
        $this->assertEquals('Host serverEu' . PHP_EOL, $file->getCurrentLine());
        $comparisonObject = ShellBuilder::new()
            ->createCommand('ssh')
            ->addShortOption('t')
            ->addShortOption('F', 'php://temp')
            ->addArgument('serverEu')
            ->addArgument(
                ShellBuilder::command('mysql')
                ->addOption('user', 'root', true, true)
                ->addOption('password', 'root', true, true)
                ->addOption('host', 'appHost', false, true)
                ->addOption('port', '3306', false, true)
                ->addArgument('appDatabase')
            )->addToBuilder();
        assert($comparisonObject instanceof ShellBuilder);
        assert($mysqlCommand instanceof ShellBuilder);
        static::assertEquals($comparisonObject->jsonSerialize(), $mysqlCommand->jsonSerialize());
    }

    public function testMysqlCommandGenerationForProductionWithCommand(): void
    {
        $globalConfig = static::getGlobalConfig();
        $commandGenerator = new CommandGenerator($globalConfig);
        $commandGenerator->setFile($file = new SplTempFileObject());
        $mysqlCommand = $commandGenerator->mysqlCommand('production', 'app', 'SELECT * FROM tablex');
        $comparisonObject = ShellBuilder::new()
            ->createCommand('ssh')
            ->addShortOption('F', 'php://temp')
            ->addArgument('serverEu')
            ->addArgument(
                ShellBuilder::command('mysql')
                    ->addOption('user', 'root', true, true)
                    ->addOption('password', 'root', true, true)
                    ->addOption('host', 'appHost', false, true)
                    ->addOption('port', '3306', false, true)
                    ->addArgument('appDatabase')
                    ->addShortOption('e', 'SELECT * FROM tablex')
            )->addToBuilder();
        assert($comparisonObject instanceof ShellBuilder);
        assert($mysqlCommand instanceof ShellBuilder);
        static::assertEquals($comparisonObject->jsonSerialize(), $mysqlCommand->jsonSerialize());
    }

    public function testMysqlCommandGenerationForLocalWithCommand(): void
    {
        $globalConfig = static::getGlobalConfig();
        $commandGenerator = new CommandGenerator($globalConfig);
        $commandGenerator->setFile($file = new SplTempFileObject());
        $mysqlCommand = $commandGenerator->mysqlCommand('local', null, 'SELECT * FROM tablex');
        $comparisonObject = ShellBuilder::command('mysql')
            ->addOption('user', 'user', true, true)
            ->addOption('password', 'pw', true, true)
            ->addOption('host', 'host', false, true)
            ->addOption('port', '3307', false, true)
            ->addArgument('database')
            ->addShortOption('e', 'SELECT * FROM tablex')->addToBuilder();
        assert($comparisonObject instanceof ShellBuilder);
        assert($mysqlCommand instanceof ShellBuilder);
        static::assertEquals($comparisonObject->jsonSerialize(), $mysqlCommand->jsonSerialize());
    }

    public function testFromAndToSameDisallowed(): void
    {
        $this->expectExceptionMessage("Source and Destination are Identical: same");
        $this->expectException(Exception::class);
        $globalConfig = static::getGlobalConfig();
        $commandGenerator = new CommandGenerator($globalConfig);
        $this->expectExceptionMessage('Source and Destination are Identical: same');
        $commandGenerator->syncCommands((new SyncOptions('same'))->setDestination('same'));
    }

    public function testProductionToLocalFromAnyThere(): void
    {
        $globalConfig = static::getGlobalConfig();
        $commandGenerator = new CommandGenerator($globalConfig);
        $commandGenerator->setFile($file = new SplTempFileObject());

        $result = $commandGenerator->syncCommands(new SyncOptions('production'));
        $this->assertSame([
            'filesystem:fileadmin' => "rsync -az -e 'ssh -F '\''php://temp'\''' 'serverEu:/var/www/production/fileadmin2/' './fileadmin/'",
            'filesystem:uploads' => "rsync -az -e 'ssh -F '\''php://temp'\''' 'serverEu:/var/www/production/uploads/' './uploads/'",
            'database:app' => "ssh -F 'php://temp' 'serverEu' 'mysqldump " . ControllerTest::MYSQLDUMP_OPTIONS . " --host='\''appHost'\'' --user='\''root'\'' --password='\''root'\'' '\''appDatabase'\'' | (echo '\''CREATE DATABASE IF NOT EXISTS `database`;USE `database`;'\'' && cat)' | mysql --host='host' --port=3307 --user='user' --password='pw'",
        ], $result);
        $expectedSshConfigString = <<<'SSH_CONFIG'
Host serverEu
  HostName server.eu
  User user

Host stagingServer
  HostName stagingServer.server.eu
  User staging

Host *
  ForwardAgent yes
  ServerAliveInterval 120


SSH_CONFIG;
        $this->assertSame($expectedSshConfigString, self::implodeTempFile($file));
    }

    public function testProductionToTestingFromAnyThere(): void
    {
        $globalConfig = static::getGlobalConfig();
        $commandGenerator = new CommandGenerator($globalConfig);
        $commandGenerator->setFile($file = new SplTempFileObject());

        $result = $commandGenerator->syncCommands((new SyncOptions('production'))->setDestination('testing'));
        $this->assertSame([
            'filesystem:fileadmin' => "ssh -F 'php://temp' 'serverEu' 'rsync -az '\''/var/www/production/fileadmin2/'\'' '\''/var/www/testing/fileadmin/'\'''",
            'filesystem:uploads' => "ssh -F 'php://temp' 'serverEu' 'rsync -az '\''/var/www/production/uploads/'\'' '\''/var/www/testing/uploads/'\'''",
            'database:app' => "ssh -F 'php://temp' 'serverEu' 'mysqldump " . ControllerTest::MYSQLDUMP_OPTIONS . " --host='\''appHost'\'' --user='\''root'\'' --password='\''root'\'' '\''appDatabase'\'' | (echo '\''CREATE DATABASE IF NOT EXISTS `database`;USE `database`;'\'' && cat) | mysql --host='\''host'\'' --port=3307 --user='\''user'\'' --password='\''pw'\'''",
        ], $result);
        $expectedSshConfigString = <<<'SSH_CONFIG'
Host serverEu
  HostName server.eu
  User user

Host stagingServer
  HostName stagingServer.server.eu
  User staging

Host *
  ForwardAgent yes
  ServerAliveInterval 120


SSH_CONFIG;
        $this->assertSame($expectedSshConfigString, self::implodeTempFile($file));
    }

    public function testLocalToLocal2FromAnyThere(): void
    {
        $globalConfig = static::getGlobalConfig();
        $commandGenerator = new CommandGenerator($globalConfig);
        $commandGenerator->setFile($file = new SplTempFileObject());

        $result = $commandGenerator->syncCommands((new SyncOptions('local'))->setDestination('local2'));
        $this->assertSame([
            'filesystem:fileadmin' => "rsync -az './fileadmin/' '../local2/fileadmin/'",
            'filesystem:uploads' => "rsync -az './uploads/' '../local2/uploads/'",
            'database:app' => "mysqldump " . ControllerTest::MYSQLDUMP_OPTIONS . " --host='host' --port=3307 --user='user' --password='pw' 'database' | (echo 'CREATE DATABASE IF NOT EXISTS `database`;USE `database`;' && cat) | mysql --host='host' --port=3307 --user='user' --password='pw'",
        ], $result);
        $expectedSshConfigString = <<<'SSH_CONFIG'
Host serverEu
  HostName server.eu
  User user

Host stagingServer
  HostName stagingServer.server.eu
  User staging

Host *
  ForwardAgent yes
  ServerAliveInterval 120


SSH_CONFIG;
        $this->assertSame($expectedSshConfigString, self::implodeTempFile($file));
    }

    public function testProductionToStagingFromAnyThere(): void
    {
        $globalConfig = static::getGlobalConfig();
        $commandGenerator = new CommandGenerator($globalConfig);
        $commandGenerator->setFile($file = new SplTempFileObject());

        $result = $commandGenerator->syncCommands((new SyncOptions('production'))->setDestination('staging'));
        $this->assertSame([
            'filesystem:fileadmin' => "rsync -az -e 'ssh -F '\''php://temp'\''' 'serverEu:/var/www/production/fileadmin2/' 'stagingServer:/var/www/staging/fileadmin/'",
            'filesystem:uploads' => "rsync -az -e 'ssh -F '\''php://temp'\''' 'serverEu:/var/www/production/uploads/' 'stagingServer:/var/www/staging/uploads/'",
            'database:app' => "ssh -F 'php://temp' 'serverEu' 'mysqldump " . ControllerTest::MYSQLDUMP_OPTIONS . " --host='\''appHost'\'' --user='\''root'\'' --password='\''root'\'' '\''appDatabase'\'' | (echo '\''CREATE DATABASE IF NOT EXISTS `database`;USE `database`;'\'' && cat)' | ssh -F 'php://temp' 'stagingServer' 'mysql --host='\''host'\'' --port=3307 --user='\''user'\'' --password='\''pw'\'''",
        ], $result);
        $expectedSshConfigString = <<<'SSH_CONFIG'
Host serverEu
  HostName server.eu
  User user

Host stagingServer
  HostName stagingServer.server.eu
  User staging

Host *
  ForwardAgent yes
  ServerAliveInterval 120


SSH_CONFIG;
        $this->assertSame($expectedSshConfigString, self::implodeTempFile($file));
    }

    public function testProductionToStagingFromStaging(): void
    {
        $globalConfig = static::getGlobalConfig();
        $commandGenerator = new CommandGenerator($globalConfig);
        $commandGenerator->setFile($file = new SplTempFileObject());

        $result = $commandGenerator->syncCommands((new SyncOptions('production'))->setDestination('staging')->setCurrentHost('stagingServer'));
        $this->assertSame([
            'filesystem:fileadmin' => "rsync -az -e 'ssh -F '\''php://temp'\''' 'serverEu:/var/www/production/fileadmin2/' '/var/www/staging/fileadmin/'",
            'filesystem:uploads' => "rsync -az -e 'ssh -F '\''php://temp'\''' 'serverEu:/var/www/production/uploads/' '/var/www/staging/uploads/'",
            'database:app' => "ssh -F 'php://temp' 'serverEu' 'mysqldump " . ControllerTest::MYSQLDUMP_OPTIONS . " --host='\''appHost'\'' --user='\''root'\'' --password='\''root'\'' '\''appDatabase'\'' | (echo '\''CREATE DATABASE IF NOT EXISTS `database`;USE `database`;'\'' && cat)' | mysql --host='host' --port=3307 --user='user' --password='pw'",
        ], $result);
        $expectedSshConfigString = <<<'SSH_CONFIG'
Host serverEu
  HostName server.eu
  User user

Host *
  ForwardAgent yes
  ServerAliveInterval 120


SSH_CONFIG;
        $this->assertSame($expectedSshConfigString, self::implodeTempFile($file));
    }

    public function testProductionToStagingFromStagingError(): void
    {
        $globalConfig = static::getGlobalConfig();
        $commandGenerator = new CommandGenerator($globalConfig);
        $commandGenerator->setFile($file = new SplTempFileObject());

        $this->expectExceptionMessage('Host noHost not found in SshConnections');
        $commandGenerator->syncCommands((new SyncOptions('production'))->setDestination('staging')->setCurrentHost('noHost'));
    }

    public function testStagingToProductionFromStaging(): void
    {
        $globalConfig = static::getGlobalConfig();
        $commandGenerator = new CommandGenerator($globalConfig);
        $commandGenerator->setFile($file = new SplTempFileObject());

        $result = $commandGenerator->syncCommands((new SyncOptions('staging'))->setDestination('production')->setCurrentHost('stagingServer'));
        $this->assertSame([
            'filesystem:fileadmin' => "rsync -az -e 'ssh -F '\''php://temp'\''' '/var/www/staging/fileadmin/' 'serverEu:/var/www/production/fileadmin2/'",
            'filesystem:uploads' => "rsync -az -e 'ssh -F '\''php://temp'\''' '/var/www/staging/uploads/' 'serverEu:/var/www/production/uploads/'",
            'database:app' => "mysqldump " . ControllerTest::MYSQLDUMP_OPTIONS . " --host='host' --port=3307 --user='user' --password='pw' 'database' | (echo 'CREATE DATABASE IF NOT EXISTS `appDatabase`;USE `appDatabase`;' && cat) | ssh -F 'php://temp' 'serverEu' 'mysql --host='\''appHost'\'' --user='\''root'\'' --password='\''root'\'''",
        ], $result);
        $expectedSshConfigString = <<<'SSH_CONFIG'
Host serverEu
  HostName server.eu
  User user

Host *
  ForwardAgent yes
  ServerAliveInterval 120


SSH_CONFIG;
        $this->assertSame($expectedSshConfigString, self::implodeTempFile($file));
    }

    public function testGzipCompression(): void
    {
        $globalConfig = static::getGlobalConfig();
        $gzipCompression = new GzipCompression();
        foreach ($globalConfig->getAppInstances() as $appInstance) {
            $appInstance->setCompressions($gzipCompression);
        }
        $commandGenerator = new CommandGenerator($globalConfig);
        $commandGenerator->setFile($file = new SplTempFileObject());

        $result = $commandGenerator->syncCommands((new SyncOptions('staging'))->setDestination('production')->setCurrentHost('stagingServer'));
        $this->assertSame([
            'filesystem:fileadmin' => "rsync -az -e 'ssh -F '\''php://temp'\''' '/var/www/staging/fileadmin/' 'serverEu:/var/www/production/fileadmin2/'",
            'filesystem:uploads' => "rsync -az -e 'ssh -F '\''php://temp'\''' '/var/www/staging/uploads/' 'serverEu:/var/www/production/uploads/'",
            'database:app' => "mysqldump " . ControllerTest::MYSQLDUMP_OPTIONS . " --host='host' --port=3307 --user='user' --password='pw' 'database' | (echo 'CREATE DATABASE IF NOT EXISTS `appDatabase`;USE `appDatabase`;' && cat) | gzip | ssh -F 'php://temp' 'serverEu' 'gunzip | mysql --host='\''appHost'\'' --user='\''root'\'' --password='\''root'\'''",
        ], $result);
    }

    public static function implodeTempFile(SplFileObject $file): string
    {
        /** @var string[] $array */
        $array = iterator_to_array($file);
        return implode('', $array);
    }
}
