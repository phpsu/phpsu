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

    public function testGetCommands(): void
    {
        $interface = new TheInterface();
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
        $result = $interface->getCommands($global, 'production', 'local', '');
        $this->assertSame([
            'rsync -e "ssh -F .phpsu/config/ssh_config" serverEu:/var/www/production/fileadmin/* ./fileadmin/',
            'rsync -e "ssh -F .phpsu/config/ssh_config" serverEu:/var/www/production/uploads/* ./uploads/',
            'ssh -F .phpsu/config/ssh_config serverEu -C "mysqldump -hhost -P3307 -uuser -ppw database" | mysql -hhost -P3307 -uuser -ppw database',
        ], $result);
        $result = $interface->getCommands($global, 'production', 'testing', '');
        $this->assertSame([
            'ssh -F .phpsu/config/ssh_config serverEu -C "rsync /var/www/production/fileadmin/* /var/www/testing/fileadmin/"',
            'ssh -F .phpsu/config/ssh_config serverEu -C "rsync /var/www/production/uploads/* /var/www/testing/uploads/"',
            'ssh -F .phpsu/config/ssh_config serverEu -C "mysqldump -hhost -P3307 -uuser -ppw database | mysql -hhost -P3307 -uuser -ppw database"',
        ], $result);
        $result = $interface->getCommands($global, 'local', 'local2', '');
        $this->assertSame([
            'rsync ./fileadmin/* ../local2/fileadmin/',
            'rsync ./uploads/* ../local2/uploads/',
            'mysqldump -hhost -P3307 -uuser -ppw database | mysql -hhost -P3307 -uuser -ppw database',
        ], $result);
        $result = $interface->getCommands($global, 'production', 'staging', '');
        $this->assertSame([
            'rsync -e "ssh -F .phpsu/config/ssh_config" serverEu:/var/www/production/fileadmin/* stagingServer:/var/www/staging/fileadmin/',
            'rsync -e "ssh -F .phpsu/config/ssh_config" serverEu:/var/www/production/uploads/* stagingServer:/var/www/staging/uploads/',
            'ssh -F .phpsu/config/ssh_config serverEu -C "mysqldump -hhost -P3307 -uuser -ppw database" | ssh -F .phpsu/config/ssh_config stagingServer -C "mysql -hhost -P3307 -uuser -ppw database"',
        ], $result);
        $result = $interface->getCommands($global, 'production', 'staging', 'stagingServer');
        $this->assertSame([
            'rsync -e "ssh -F .phpsu/config/ssh_config" serverEu:/var/www/production/fileadmin/* /var/www/staging/fileadmin/',
            'rsync -e "ssh -F .phpsu/config/ssh_config" serverEu:/var/www/production/uploads/* /var/www/staging/uploads/',
            'ssh -F .phpsu/config/ssh_config serverEu -C "mysqldump -hhost -P3307 -uuser -ppw database" | mysql -hhost -P3307 -uuser -ppw database',
        ], $result);
    }
}
