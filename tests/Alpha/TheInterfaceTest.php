<?php
declare(strict_types=1);

namespace PHPSu\Tests\Alpha;

use PHPSu\Alpha\AppInstance;
use PHPSu\Alpha\GlobalConfig;
use PHPSu\Alpha\SshConnection;
use PHPSu\Alpha\SshConnections;
use PHPSu\Alpha\TheInterface;
use PHPUnit\Framework\TestCase;

class TheInterfaceTest extends TestCase
{
    public function testProductionToLocalFromAnyThere(): void
    {
        $interface = new TheInterface();
        $interface->setFile($file = new \SplTempFileObject());
        $global = $this->getGlobalConfig();

        $result = $interface->getCommands($global, 'production', 'local', '');
        $this->assertSame([
            'rsync -e "ssh -F php://temp" serverEu:/var/www/production/fileadmin/* ./fileadmin/',
            'rsync -e "ssh -F php://temp" serverEu:/var/www/production/uploads/* ./uploads/',
            'ssh -F php://temp serverEu -C "mysqldump -hhost -P3307 -uuser -ppw database" | mysql -hhost -P3307 -uuser -ppw database',
        ], $result);
        $expectedSshConfigString = <<<'SSH_CONFIG'
Host serverEu
  HostName server.eu
  IdentityFile docker/testCaseD/id_rsa
  User user

Host stagingServer
  HostName stagingServer.server.eu
  IdentityFile docker/testCaseD/id_rsa
  User staging


SSH_CONFIG;
        $this->assertSame($expectedSshConfigString, implode('', iterator_to_array($file)));
    }

    public function testProductionToTestingFromAnyThere(): void
    {
        $interface = new TheInterface();
        $interface->setFile($file = new \SplTempFileObject());
        $global = $this->getGlobalConfig();

        $result = $interface->getCommands($global, 'production', 'testing', '');
        $this->assertSame([
            'ssh -F php://temp serverEu -C "rsync /var/www/production/fileadmin/* /var/www/testing/fileadmin/"',
            'ssh -F php://temp serverEu -C "rsync /var/www/production/uploads/* /var/www/testing/uploads/"',
            'ssh -F php://temp serverEu -C "mysqldump -hhost -P3307 -uuser -ppw database | mysql -hhost -P3307 -uuser -ppw database"',
        ], $result);
        $expectedSshConfigString = <<<'SSH_CONFIG'
Host serverEu
  HostName server.eu
  IdentityFile docker/testCaseD/id_rsa
  User user

Host stagingServer
  HostName stagingServer.server.eu
  IdentityFile docker/testCaseD/id_rsa
  User staging


SSH_CONFIG;
        $this->assertSame($expectedSshConfigString, implode('', iterator_to_array($file)));
    }

    public function testLocalToLocal2FromAnyThere(): void
    {
        $interface = new TheInterface();
        $interface->setFile($file = new \SplTempFileObject());
        $global = $this->getGlobalConfig();

        $result = $interface->getCommands($global, 'local', 'local2', '');
        $this->assertSame([
            'rsync ./fileadmin/* ../local2/fileadmin/',
            'rsync ./uploads/* ../local2/uploads/',
            'mysqldump -hhost -P3307 -uuser -ppw database | mysql -hhost -P3307 -uuser -ppw database',
        ], $result);
        $expectedSshConfigString = <<<'SSH_CONFIG'
Host serverEu
  HostName server.eu
  IdentityFile docker/testCaseD/id_rsa
  User user

Host stagingServer
  HostName stagingServer.server.eu
  IdentityFile docker/testCaseD/id_rsa
  User staging


SSH_CONFIG;
        $this->assertSame($expectedSshConfigString, implode('', iterator_to_array($file)));
    }

    public function testProductionToStagingFromAnyThere(): void
    {
        $interface = new TheInterface();
        $interface->setFile($file = new \SplTempFileObject());
        $global = $this->getGlobalConfig();

        $result = $interface->getCommands($global, 'production', 'staging', '');
        $this->assertSame([
            'rsync -e "ssh -F php://temp" serverEu:/var/www/production/fileadmin/* stagingServer:/var/www/staging/fileadmin/',
            'rsync -e "ssh -F php://temp" serverEu:/var/www/production/uploads/* stagingServer:/var/www/staging/uploads/',
            'ssh -F php://temp serverEu -C "mysqldump -hhost -P3307 -uuser -ppw database" | ssh -F php://temp stagingServer -C "mysql -hhost -P3307 -uuser -ppw database"',
        ], $result);
        $expectedSshConfigString = <<<'SSH_CONFIG'
Host serverEu
  HostName server.eu
  IdentityFile docker/testCaseD/id_rsa
  User user

Host stagingServer
  HostName stagingServer.server.eu
  IdentityFile docker/testCaseD/id_rsa
  User staging


SSH_CONFIG;
        $this->assertSame($expectedSshConfigString, implode('', iterator_to_array($file)));
    }

    public function testProductionToStagingFromStaging(): void
    {
        $interface = new TheInterface();
        $interface->setFile($file = new \SplTempFileObject());
        $global = $this->getGlobalConfig();

        $result = $interface->getCommands($global, 'production', 'staging', 'stagingServer');
        $this->assertSame([
            'rsync -e "ssh -F php://temp" serverEu:/var/www/production/fileadmin/* /var/www/staging/fileadmin/',
            'rsync -e "ssh -F php://temp" serverEu:/var/www/production/uploads/* /var/www/staging/uploads/',
            'ssh -F php://temp serverEu -C "mysqldump -hhost -P3307 -uuser -ppw database" | mysql -hhost -P3307 -uuser -ppw database',
        ], $result);
        $expectedSshConfigString = <<<'SSH_CONFIG'
Host serverEu
  HostName server.eu
  IdentityFile docker/testCaseD/id_rsa
  User user


SSH_CONFIG;
        $this->assertSame($expectedSshConfigString, implode('', iterator_to_array($file)));
    }

    /**
     * @return GlobalConfig
     */
    private function getGlobalConfig(): GlobalConfig
    {
        $global = new GlobalConfig();
        $global->fileSystems = new \stdClass();
        $global->fileSystems->fileadmin = 'fileadmin';
        $global->fileSystems->uploads = 'uploads';
        $global->databases = new \stdClass();
        $global->databases->main = 'mysql://user:pw@host:3307/database';
        $global->sshConnections = new SshConnections();
        $global->sshConnections->addConnection((new SshConnection())->setHost('serverEu')->setUrl('user@server.eu')->setIdentityFile('docker/testCaseD/id_rsa'));
        $global->sshConnections->addConnection((new SshConnection())->setHost('stagingServer')->setUrl('staging@stagingServer.server.eu')->setIdentityFile('docker/testCaseD/id_rsa'));
        $global->appInstances = new \stdClass();
        $global->appInstances->production = (new AppInstance())->setName('production')->setHost('serverEu')->setPath('/var/www/production');
        $global->appInstances->staging = (new AppInstance())->setName('staging')->setHost('stagingServer')->setPath('/var/www/staging');
        $global->appInstances->testing = (new AppInstance())->setName('testing')->setHost('serverEu')->setPath('/var/www/testing');
        $global->appInstances->local = (new AppInstance())->setName('local')->setHost('')->setPath('./');
        $global->appInstances->local2 = (new AppInstance())->setName('local2')->setHost('')->setPath('../local2');
        return $global;
    }
}
