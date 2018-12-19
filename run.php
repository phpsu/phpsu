<?php
declare(strict_types=1);

namespace Run;

use PHPSu\Alpha\AppInstance;
use PHPSu\Alpha\FileSystem;
use PHPSu\Alpha\GlobalConfig;
use PHPSu\Alpha\SshConnection;
use PHPSu\Chi\Runner;

require_once "vendor/autoload.php";

$config = new GlobalConfig();
$config->addFilesystem((new FileSystem())->setName('A')->setPath('A'));
$config->addSshConnection((new SshConnection())->setHost('hosta')->setUrl('ssh://user:user@localhost:2208')->setIdentityFile('docker/testCaseD/id_rsa'));
$config->addAppInstance((new AppInstance())->setName('production')->setHost('hosta')->setPath('~/'));
$config->addAppInstance((new AppInstance())->setName('local'));
(new Runner())->run($config, 'production', 'local', '');
