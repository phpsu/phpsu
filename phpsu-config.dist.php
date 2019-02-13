<?php
declare(strict_types=1);

$config = new \PHPSu\Config\GlobalConfig();
$config->addFilesystem('Image Uploads', 'var/storage')
    ->addExclude('*.mp4')
    ->addExclude('*.mp3')
    ->addExclude('*.zip');
$config->addSshConnection('hostA', 'ssh://user@localhost:2208');
$config->addAppInstance('production', 'hostA', '/var/www/')
    ->addDatabase('app', 'mysql://root:password@127.0.0.1:3307/production01db')
    ->addExclude('table1')
    ->addExclude('table2');
$config->addAppInstance('local')
    ->addDatabase('app', 'mysql://root:root@127.0.0.1/testingLocal');
return $config;
