<?php
declare(strict_types=1);

$globalConfig = new \PHPSu\Config\GlobalConfig();
$globalConfig->addFilesystem('var/storage', 'var/storage');
$globalConfig->addAppInstance('production', '', 'testProduction');
$globalConfig->addAppInstance('local', '', 'testLocal');
return $globalConfig;
