<?php
declare(strict_types=1);

namespace PHPSu\Tests\Alpha;

use PHPSu\Alpha\AppInstance;
use PHPSu\Alpha\RsyncCommand;
use PHPSu\Alpha\SshConnection;
use PHPUnit\Framework\TestCase;

final class GlobalConfigTest extends TestCase
{
    public function testRsyncFromGlobalConfig()
    {
        $global = new \stdClass();
        $global->fileSystems = new \stdClass();
        $global->fileSystems->fileadmin = 'fileadmin';
        $global->fileSystems->uploads = 'uploads';
        $global->sshConnections = new \stdClass();
        $global->sshConnections->serverEu = (new SshConnection())->setHost('serverEu')->setUrl('user@server.eu')->setIdentityFile('docker/testCaseD/id_rsa');
        $global->appInstances = new \stdClass();
        $global->appInstances->production = (new AppInstance())->setName('production')->setHost('serverEu')->setPath('/var/www/production');
        $global->appInstances->testing = (new AppInstance())->setName('testing')->setHost('serverEu')->setPath('/var/www/testing');

        $rsyncConfigs = \PHPSu\Alpha\RsyncConfig::fromGlobal($global, 'production', 'testing');
        $this->assertEquals([
            (new RsyncCommand())->setFrom('serverEu:/var/www/production/fileadmin/*')->setTo('serverEu:/var/www/testing/fileadmin/'),
            (new RsyncCommand())->setFrom('serverEu:/var/www/production/uploads/*')->setTo('serverEu:/var/www/testing/uploads/'),
        ], $rsyncConfigs);
    }
}
