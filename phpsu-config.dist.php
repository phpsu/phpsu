<?php
declare(strict_types=1);

use PHPSu\Alpha\GlobalConfig;

$config = new GlobalConfig();
$config->addFilesystem('A', 'A');
$config->addSshConnection('hosta', 'ssh://user:user@localhost:2208');
$config->addAppInstance('production', '~/', 'hosta');
$config->addAppInstance('local');
return $config;
