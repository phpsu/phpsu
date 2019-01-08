<?php
declare(strict_types=1);

use PHPSu\Alpha\AppInstance;
use PHPSu\Alpha\FileSystem;
use PHPSu\Alpha\GlobalConfig;
use PHPSu\Alpha\SshConnection;

$config = new GlobalConfig();
$config->addFilesystem((new FileSystem())->setName('A')->setPath('A'));
$config->addSshConnection((new SshConnection())->setHost('hosta')->setUrl('ssh://user:user@localhost:2208')->setIdentityFile('docker/testCaseD/id_rsa'));
$config->addAppInstance((new AppInstance())->setName('production')->setHost('hosta')->setPath('~/'));
$config->addAppInstance((new AppInstance())->setName('local'));
return $config;
