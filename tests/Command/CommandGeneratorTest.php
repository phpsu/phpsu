<?php
declare(strict_types=1);

namespace PHPSu\Tests\Command;

use PHPSu\Command\CommandGenerator;
use PHPSu\Config\AppInstance;
use PHPSu\Config\Database;
use PHPSu\Config\FileSystem;
use PHPSu\Config\GlobalConfig;
use PHPSu\Config\SshConnection;
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

    public function testProductionToLocalFromAnyThere(): void
    {
        $globalConfig = static::getGlobalConfig();
        $interface = new CommandGenerator($globalConfig);
        $interface->setFile($file = new \SplTempFileObject());

        $result = $interface->syncCommands('production', 'local', '');
        $this->assertSame([
            'filesystem:fileadmin' => 'rsync -avz -e "ssh -F php://temp" serverEu:/var/www/production/fileadmin2/* ./fileadmin/',
            'filesystem:uploads' => 'rsync -avz -e "ssh -F php://temp" serverEu:/var/www/production/uploads/* ./uploads/',
            'database:app' => 'ssh -F php://temp serverEu -C "mysqldump --skip-comments --extended-insert -happHost -P3306 -uroot -proot appDatabase" | mysql -hhost -P3307 -uuser -ppw database',
        ], $result);
        $expectedSshConfigString = <<<'SSH_CONFIG'
Host serverEu
  HostName server.eu
  User user

Host stagingServer
  HostName stagingServer.server.eu
  User staging


SSH_CONFIG;
        $this->assertSame($expectedSshConfigString, implode('', iterator_to_array($file)));
    }

    public function testProductionToTestingFromAnyThere(): void
    {
        $globalConfig = static::getGlobalConfig();
        $interface = new CommandGenerator($globalConfig);
        $interface->setFile($file = new \SplTempFileObject());

        $result = $interface->syncCommands('production', 'testing', '');
        $this->assertSame([
            'filesystem:fileadmin' => 'ssh -F php://temp serverEu -C "rsync -avz /var/www/production/fileadmin2/* /var/www/testing/fileadmin/"',
            'filesystem:uploads' => 'ssh -F php://temp serverEu -C "rsync -avz /var/www/production/uploads/* /var/www/testing/uploads/"',
            'database:app' => 'ssh -F php://temp serverEu -C "mysqldump --skip-comments --extended-insert -happHost -P3306 -uroot -proot appDatabase | mysql -hhost -P3307 -uuser -ppw database"',
        ], $result);
        $expectedSshConfigString = <<<'SSH_CONFIG'
Host serverEu
  HostName server.eu
  User user

Host stagingServer
  HostName stagingServer.server.eu
  User staging


SSH_CONFIG;
        $this->assertSame($expectedSshConfigString, implode('', iterator_to_array($file)));
    }

    public function testLocalToLocal2FromAnyThere(): void
    {
        $globalConfig = static::getGlobalConfig();
        $interface = new CommandGenerator($globalConfig);
        $interface->setFile($file = new \SplTempFileObject());

        $result = $interface->syncCommands('local', 'local2', '');
        $this->assertSame([
            'filesystem:fileadmin' => 'rsync -avz ./fileadmin/* ../local2/fileadmin/',
            'filesystem:uploads' => 'rsync -avz ./uploads/* ../local2/uploads/',
            'database:app' => 'mysqldump --skip-comments --extended-insert -hhost -P3307 -uuser -ppw database | mysql -hhost -P3307 -uuser -ppw database',
        ], $result);
        $expectedSshConfigString = <<<'SSH_CONFIG'
Host serverEu
  HostName server.eu
  User user

Host stagingServer
  HostName stagingServer.server.eu
  User staging


SSH_CONFIG;
        $this->assertSame($expectedSshConfigString, implode('', iterator_to_array($file)));
    }

    public function testProductionToStagingFromAnyThere(): void
    {
        $globalConfig = static::getGlobalConfig();
        $interface = new CommandGenerator($globalConfig);
        $interface->setFile($file = new \SplTempFileObject());

        $result = $interface->syncCommands('production', 'staging', '');
        $this->assertSame([
            'filesystem:fileadmin' => 'rsync -avz -e "ssh -F php://temp" serverEu:/var/www/production/fileadmin2/* stagingServer:/var/www/staging/fileadmin/',
            'filesystem:uploads' => 'rsync -avz -e "ssh -F php://temp" serverEu:/var/www/production/uploads/* stagingServer:/var/www/staging/uploads/',
            'database:app' => 'ssh -F php://temp serverEu -C "mysqldump --skip-comments --extended-insert -happHost -P3306 -uroot -proot appDatabase" | ssh -F php://temp stagingServer -C "mysql -hhost -P3307 -uuser -ppw database"',
        ], $result);
        $expectedSshConfigString = <<<'SSH_CONFIG'
Host serverEu
  HostName server.eu
  User user

Host stagingServer
  HostName stagingServer.server.eu
  User staging


SSH_CONFIG;
        $this->assertSame($expectedSshConfigString, implode('', iterator_to_array($file)));
    }

    public function testProductionToStagingFromStaging(): void
    {
        $globalConfig = static::getGlobalConfig();
        $interface = new CommandGenerator($globalConfig);
        $interface->setFile($file = new \SplTempFileObject());

        $result = $interface->syncCommands('production', 'staging', 'stagingServer');
        $this->assertSame([
            'filesystem:fileadmin' => 'rsync -avz -e "ssh -F php://temp" serverEu:/var/www/production/fileadmin2/* /var/www/staging/fileadmin/',
            'filesystem:uploads' => 'rsync -avz -e "ssh -F php://temp" serverEu:/var/www/production/uploads/* /var/www/staging/uploads/',
            'database:app' => 'ssh -F php://temp serverEu -C "mysqldump --skip-comments --extended-insert -happHost -P3306 -uroot -proot appDatabase" | mysql -hhost -P3307 -uuser -ppw database',
        ], $result);
        $expectedSshConfigString = <<<'SSH_CONFIG'
Host serverEu
  HostName server.eu
  User user


SSH_CONFIG;
        $this->assertSame($expectedSshConfigString, implode('', iterator_to_array($file)));
    }

    public function testStagingToProductionFromStaging(): void
    {
        $globalConfig = static::getGlobalConfig();
        $interface = new CommandGenerator($globalConfig);
        $interface->setFile($file = new \SplTempFileObject());

        $result = $interface->syncCommands('staging', 'production', 'stagingServer');
        $this->assertSame([
            'filesystem:fileadmin' => 'rsync -avz -e "ssh -F php://temp" /var/www/staging/fileadmin/* serverEu:/var/www/production/fileadmin2/',
            'filesystem:uploads' => 'rsync -avz -e "ssh -F php://temp" /var/www/staging/uploads/* serverEu:/var/www/production/uploads/',
            'database:app' => 'mysqldump --skip-comments --extended-insert -hhost -P3307 -uuser -ppw database | ssh -F php://temp serverEu -C "mysql -happHost -P3306 -uroot -proot appDatabase"',
        ], $result);
        $expectedSshConfigString = <<<'SSH_CONFIG'
Host serverEu
  HostName server.eu
  User user


SSH_CONFIG;
        $this->assertSame($expectedSshConfigString, implode('', iterator_to_array($file)));
    }
}
