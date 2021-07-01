<?php

declare(strict_types=1);

use PHPSu\Config\GlobalConfig;

$globalConfig = new GlobalConfig();
$globalConfig->addFilesystem('var/storage', 'var/storage');
$globalConfig->addAppInstance('production', '', 'testProduction');
$globalConfig->addAppInstance('local', '', 'testLocal');
return $globalConfig;
