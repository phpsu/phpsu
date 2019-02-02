<?php
declare(strict_types=1);

$config = new \PHPSu\Config\GlobalConfig();
$config->addFilesystem('Image Uploads', 'var/storage');
$config->addDatabase('app', 'mysql://root:password@127.0.0.1:3307/production01db');
$config->addSshConnection('hostA', 'ssh://user@localhost:2208');
$config->addAppInstance('production', 'hostA', '/var/www/');
$config->addAppInstance('local');
return $config;
