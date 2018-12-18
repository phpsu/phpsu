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
        $global->sshConnections = new SshConnections();
        $global->sshConnections->addConnection((new SshConnection())->setHost('serverEu')->setUrl('user@server.eu')->setIdentityFile('docker/testCaseD/id_rsa'));
        $global->appInstances = new \stdClass();
        $global->appInstances->production = (new AppInstance())->setName('production')->setHost('serverEu')->setPath('/var/www/production');
        $global->appInstances->testing = (new AppInstance())->setName('testing')->setHost('serverEu')->setPath('/var/www/testing');
        $global->appInstances->local = (new AppInstance())->setName('local')->setHost('local')->setPath('./');
        $result = $interface->getCommands($global, 'production', 'local', 'local');
        $this->assertSame([
            'rsync  -e "ssh -F .phpsu/config/ssh_config" serverEu:/var/www/production/fileadmin/* ./fileadmin/',
            'rsync  -e "ssh -F .phpsu/config/ssh_config" serverEu:/var/www/production/uploads/* ./uploads/',
        ], $result);
        $result = $interface->getCommands($global, 'production', 'testing', 'local');
        $this->assertSame([
            'rsync  -e "ssh -F .phpsu/config/ssh_config" serverEu:/var/www/production/fileadmin/* serverEu:/var/www/testing/fileadmin/',
            'rsync  -e "ssh -F .phpsu/config/ssh_config" serverEu:/var/www/production/uploads/* serverEu:/var/www/testing/uploads/',
        ], $result);
    }
}
