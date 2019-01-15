<?php
declare(strict_types=1);

$config = new \PHPSu\Config\GlobalConfig();
$config->addFilesystem('Image Uploads', 'var/storage');
$config->addSshConnection('hostA', 'ssh://user@localhost:2208');
$config->addAppInstance('production', 'hostA', '/var/www/');
$config->addAppInstance('local');
return $config;
