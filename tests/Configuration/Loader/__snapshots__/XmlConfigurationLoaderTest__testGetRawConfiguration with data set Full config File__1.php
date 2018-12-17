<?php return PHPSu\Configuration\RawConfiguration\RawConfigurationDto::__set_state(array(
   'hosts' =>
  PHPSu\Configuration\RawConfiguration\RawHostBag::__set_state(array(
     'bagContent' =>
    array (
      'Production' =>
      PHPSu\Configuration\RawConfiguration\RawHostDto::__set_state(array(
         'console' =>
        PHPSu\Configuration\RawConfiguration\RawConsoleDto::__set_state(array(
           'options' =>
          PHPSu\Configuration\RawConfiguration\RawOptionBag::__set_state(array(
             'optionValues' =>
            array (
              'host' => 'example.com',
              'user' => 'example',
              'port' => '22',
              'rootDir' => '/srv/www/example/www.example.com',
            ),
          )),
           'type' => 'ssh',
           'name' => '',
        )),
         'filesystems' =>
        PHPSu\Configuration\RawConfiguration\RawFilesystemBag::__set_state(array(
           'bagContent' =>
          array (
          ),
           'itemClass' => 'PHPSu\\Configuration\\RawConfiguration\\RawFilesystemDto',
        )),
         'databases' =>
        PHPSu\Configuration\RawConfiguration\RawDatabaseBag::__set_state(array(
           'bagContent' =>
          array (
          ),
           'itemClass' => 'PHPSu\\Configuration\\RawConfiguration\\RawDatabaseDto',
        )),
         'name' => 'Production',
      )),
      'Testing' =>
      PHPSu\Configuration\RawConfiguration\RawHostDto::__set_state(array(
         'console' =>
        PHPSu\Configuration\RawConfiguration\RawConsoleDto::__set_state(array(
           'options' =>
          PHPSu\Configuration\RawConfiguration\RawOptionBag::__set_state(array(
             'optionValues' =>
            array (
              'host' => 'example.com',
              'user' => 'example',
              'port' => '22',
              'rootDir' => '/srv/www/example/test.example.com',
            ),
          )),
           'type' => 'ssh',
           'name' => '',
        )),
         'filesystems' =>
        PHPSu\Configuration\RawConfiguration\RawFilesystemBag::__set_state(array(
           'bagContent' =>
          array (
          ),
           'itemClass' => 'PHPSu\\Configuration\\RawConfiguration\\RawFilesystemDto',
        )),
         'databases' =>
        PHPSu\Configuration\RawConfiguration\RawDatabaseBag::__set_state(array(
           'bagContent' =>
          array (
          ),
           'itemClass' => 'PHPSu\\Configuration\\RawConfiguration\\RawDatabaseDto',
        )),
         'name' => 'Testing',
      )),
      'vogel' =>
      PHPSu\Configuration\RawConfiguration\RawHostDto::__set_state(array(
         'console' =>
        PHPSu\Configuration\RawConfiguration\RawConsoleDto::__set_state(array(
           'options' =>
          PHPSu\Configuration\RawConfiguration\RawOptionBag::__set_state(array(
             'optionValues' =>
            array (
              'host' => '10.50.1.223',
              'user' => 'user',
              'rootDir' => '/var/www/project_example',
            ),
          )),
           'type' => 'ssh',
           'name' => '',
        )),
         'filesystems' =>
        PHPSu\Configuration\RawConfiguration\RawFilesystemBag::__set_state(array(
           'bagContent' =>
          array (
            'fileadmin' =>
            PHPSu\Configuration\RawConfiguration\RawFilesystemDto::__set_state(array(
               'options' =>
              PHPSu\Configuration\RawConfiguration\RawOptionBag::__set_state(array(
                 'optionValues' =>
                array (
                  'directory' => 'public/fileadmin/',
                ),
              )),
               'type' => 'directory',
               'name' => 'fileadmin',
            )),
          ),
           'itemClass' => 'PHPSu\\Configuration\\RawConfiguration\\RawFilesystemDto',
        )),
         'databases' =>
        PHPSu\Configuration\RawConfiguration\RawDatabaseBag::__set_state(array(
           'bagContent' =>
          array (
            '' =>
            PHPSu\Configuration\RawConfiguration\RawDatabaseDto::__set_state(array(
               'options' =>
              PHPSu\Configuration\RawConfiguration\RawOptionBag::__set_state(array(
                 'optionValues' =>
                array (
                  'host' => 'db',
                ),
              )),
               'type' => 'mysql',
               'name' => '',
            )),
          ),
           'itemClass' => 'PHPSu\\Configuration\\RawConfiguration\\RawDatabaseDto',
        )),
         'name' => 'vogel',
      )),
    ),
     'itemClass' => 'PHPSu\\Configuration\\RawConfiguration\\RawHostDto',
  )),
   'filesystems' =>
  PHPSu\Configuration\RawConfiguration\RawFilesystemBag::__set_state(array(
     'bagContent' =>
    array (
      'fileadmin' =>
      PHPSu\Configuration\RawConfiguration\RawFilesystemDto::__set_state(array(
         'options' =>
        PHPSu\Configuration\RawConfiguration\RawOptionBag::__set_state(array(
           'optionValues' =>
          array (
            'directory' => 'fileadmin/',
          ),
        )),
         'type' => 'directory',
         'name' => 'fileadmin',
      )),
    ),
     'itemClass' => 'PHPSu\\Configuration\\RawConfiguration\\RawFilesystemDto',
  )),
   'databases' =>
  PHPSu\Configuration\RawConfiguration\RawDatabaseBag::__set_state(array(
     'bagContent' =>
    array (
    ),
     'itemClass' => 'PHPSu\\Configuration\\RawConfiguration\\RawDatabaseDto',
  )),
));
