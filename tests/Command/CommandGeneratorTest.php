<?php
declare(strict_types=1);

namespace PHPSu\Tests\Command;

use PHPSu\Command\CommandGenerator;
use PHPSu\Config\AppInstance;
use PHPSu\Config\Database;
use PHPSu\Config\FileSystem;
use PHPSu\Config\GlobalConfig;
use PHPSu\Config\SshConnection;
use PHPSu\Options\SyncOptions;
use PHPUnit\Framework\TestCase;

final class CommandGeneratorTest extends TestCase
{
    private static function getGlobalConfig(): GlobalConfig
    {
        $globalConfig = new GlobalConfig();
        $globalConfig->addFilesystemObject((new FileSystem())->setName('fileadmin')->setPath('fileadmin'));
        $globalConfig->addFilesystemObject((new FileSystem())->setName('uploads')->setPath('uploads'));
        $globalConfig->addDatabaseObject((new Database())->setName('app')->setUrl('mysql://user:pw@host:3307/database'));
        $globalConfig->addSshConnection('serverEu', 'user@server.eu');
        $globalConfig->addSshConnectionObject((new SshConnection())->setHost('stagingServer')->setUrl('staging@stagingServer.server.eu'));
        $globalConfig->addAppInstanceObject((new AppInstance())->setName('production')->setHost('serverEu')->setPath('/var/www/production')->addFilesystemObject(
            (new FileSystem())->setName('fileadmin')->setPath('fileadmin2')
        )->addDatabaseObject(
            (new Database())->setName('app')->setUrl('mysql://root:root@appHost/appDatabase')
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
        $commandGenerator->setFile($file = new \SplTempFileObject());
        $result = $commandGenerator->sshCommand('production', '', '');
        $this->assertSame("ssh -F 'php://temp' 'serverEu' -t 'cd '\''/var/www/production'\''; bash --login'", $result);
    }

    public function testSshWithCommand(): void
    {
        $globalConfig = static::getGlobalConfig();
        $commandGenerator = new CommandGenerator($globalConfig);
        $commandGenerator->setFile($file = new \SplTempFileObject());
        $result = $commandGenerator->sshCommand('production', '', 'ls -alh --color');
        $this->assertSame("ssh -F 'php://temp' 'serverEu' -t 'cd '\''/var/www/production'\''; ls -alh --color'", $result);
        $result = $commandGenerator->sshCommand('production', '', 'echo "test"');
        $this->assertSame("ssh -F 'php://temp' 'serverEu' -t 'cd '\''/var/www/production'\''; echo \"test\"'", $result);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Source and Destination are Identical: same
     */
    public function testFromAndToSameDisallowed(): void
    {
        $globalConfig = static::getGlobalConfig();
        $commandGenerator = new CommandGenerator($globalConfig);
        $commandGenerator->syncCommands((new SyncOptions('same'))->setDestination('same'));
    }

    public function testProductionToLocalFromAnyThere(): void
    {
        $globalConfig = static::getGlobalConfig();
        $commandGenerator = new CommandGenerator($globalConfig);
        $commandGenerator->setFile($file = new \SplTempFileObject());

        $result = $commandGenerator->syncCommands(new SyncOptions('production'));
        $this->assertSame([
            'filesystem:fileadmin' => "rsync -az -e 'ssh -F '\''php://temp'\''' 'serverEu:/var/www/production/fileadmin2/*' './fileadmin/'",
            'filesystem:uploads' => "rsync -az -e 'ssh -F '\''php://temp'\''' 'serverEu:/var/www/production/uploads/*' './uploads/'",
            'database:app' => "ssh -F 'php://temp' 'serverEu' 'mysqldump --opt --skip-comments -h'\''appHost'\'' -u'\''root'\'' -p'\''root'\'' '\''appDatabase'\''' | mysql -h'host' -P3307 -u'user' -p'pw' 'database'",
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


SSH_CONFIG;
        $this->assertSame($expectedSshConfigString, implode('', iterator_to_array($file)));
    }

    public function testProductionToTestingFromAnyThere(): void
    {
        $globalConfig = static::getGlobalConfig();
        $commandGenerator = new CommandGenerator($globalConfig);
        $commandGenerator->setFile($file = new \SplTempFileObject());

        $result = $commandGenerator->syncCommands((new SyncOptions('production'))->setDestination('testing'));
        $this->assertSame([
            'filesystem:fileadmin' => "ssh -F 'php://temp' 'serverEu' 'rsync -az '\''/var/www/production/fileadmin2/*'\'' '\''/var/www/testing/fileadmin/'\'''",
            'filesystem:uploads' => "ssh -F 'php://temp' 'serverEu' 'rsync -az '\''/var/www/production/uploads/*'\'' '\''/var/www/testing/uploads/'\'''",
            'database:app' => "ssh -F 'php://temp' 'serverEu' 'mysqldump --opt --skip-comments -h'\''appHost'\'' -u'\''root'\'' -p'\''root'\'' '\''appDatabase'\'' | mysql -h'\''host'\'' -P3307 -u'\''user'\'' -p'\''pw'\'' '\''database'\'''",
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


SSH_CONFIG;
        $this->assertSame($expectedSshConfigString, implode('', iterator_to_array($file)));
    }

    public function testLocalToLocal2FromAnyThere(): void
    {
        $globalConfig = static::getGlobalConfig();
        $commandGenerator = new CommandGenerator($globalConfig);
        $commandGenerator->setFile($file = new \SplTempFileObject());

        $result = $commandGenerator->syncCommands((new SyncOptions('local'))->setDestination('local2'));
        $this->assertSame([
            'filesystem:fileadmin' => "rsync -az './fileadmin/*' '../local2/fileadmin/'",
            'filesystem:uploads' => "rsync -az './uploads/*' '../local2/uploads/'",
            'database:app' => "mysqldump --opt --skip-comments -h'host' -P3307 -u'user' -p'pw' 'database' | mysql -h'host' -P3307 -u'user' -p'pw' 'database'",
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


SSH_CONFIG;
        $this->assertSame($expectedSshConfigString, implode('', iterator_to_array($file)));
    }

    public function testProductionToStagingFromAnyThere(): void
    {
        $globalConfig = static::getGlobalConfig();
        $commandGenerator = new CommandGenerator($globalConfig);
        $commandGenerator->setFile($file = new \SplTempFileObject());

        $result = $commandGenerator->syncCommands((new SyncOptions('production'))->setDestination('staging'));
        $this->assertSame([
            'filesystem:fileadmin' => "rsync -az -e 'ssh -F '\''php://temp'\''' 'serverEu:/var/www/production/fileadmin2/*' 'stagingServer:/var/www/staging/fileadmin/'",
            'filesystem:uploads' => "rsync -az -e 'ssh -F '\''php://temp'\''' 'serverEu:/var/www/production/uploads/*' 'stagingServer:/var/www/staging/uploads/'",
            'database:app' => "ssh -F 'php://temp' 'serverEu' 'mysqldump --opt --skip-comments -h'\''appHost'\'' -u'\''root'\'' -p'\''root'\'' '\''appDatabase'\''' | ssh -F 'php://temp' 'stagingServer' 'mysql -h'\''host'\'' -P3307 -u'\''user'\'' -p'\''pw'\'' '\''database'\'''",
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


SSH_CONFIG;
        $this->assertSame($expectedSshConfigString, implode('', iterator_to_array($file)));
    }

    public function testProductionToStagingFromStaging(): void
    {
        $globalConfig = static::getGlobalConfig();
        $commandGenerator = new CommandGenerator($globalConfig);
        $commandGenerator->setFile($file = new \SplTempFileObject());

        $result = $commandGenerator->syncCommands((new SyncOptions('production'))->setDestination('staging')->setCurrentHost('stagingServer'));
        $this->assertSame([
            'filesystem:fileadmin' => "rsync -az -e 'ssh -F '\''php://temp'\''' 'serverEu:/var/www/production/fileadmin2/*' '/var/www/staging/fileadmin/'",
            'filesystem:uploads' => "rsync -az -e 'ssh -F '\''php://temp'\''' 'serverEu:/var/www/production/uploads/*' '/var/www/staging/uploads/'",
            'database:app' => "ssh -F 'php://temp' 'serverEu' 'mysqldump --opt --skip-comments -h'\''appHost'\'' -u'\''root'\'' -p'\''root'\'' '\''appDatabase'\''' | mysql -h'host' -P3307 -u'user' -p'pw' 'database'",
        ], $result);
        $expectedSshConfigString = <<<'SSH_CONFIG'
Host serverEu
  HostName server.eu
  User user

Host *
  ForwardAgent yes


SSH_CONFIG;
        $this->assertSame($expectedSshConfigString, implode('', iterator_to_array($file)));
    }

    public function testStagingToProductionFromStaging(): void
    {
        $globalConfig = static::getGlobalConfig();
        $commandGenerator = new CommandGenerator($globalConfig);
        $commandGenerator->setFile($file = new \SplTempFileObject());

        $result = $commandGenerator->syncCommands((new SyncOptions('staging'))->setDestination('production')->setCurrentHost('stagingServer'));
        $this->assertSame([
            'filesystem:fileadmin' => "rsync -az -e 'ssh -F '\''php://temp'\''' '/var/www/staging/fileadmin/*' 'serverEu:/var/www/production/fileadmin2/'",
            'filesystem:uploads' => "rsync -az -e 'ssh -F '\''php://temp'\''' '/var/www/staging/uploads/*' 'serverEu:/var/www/production/uploads/'",
            'database:app' => "mysqldump --opt --skip-comments -h'host' -P3307 -u'user' -p'pw' 'database' | ssh -F 'php://temp' 'serverEu' 'mysql -h'\''appHost'\'' -u'\''root'\'' -p'\''root'\'' '\''appDatabase'\'''",
        ], $result);
        $expectedSshConfigString = <<<'SSH_CONFIG'
Host serverEu
  HostName server.eu
  User user

Host *
  ForwardAgent yes


SSH_CONFIG;
        $this->assertSame($expectedSshConfigString, implode('', iterator_to_array($file)));
    }
}
