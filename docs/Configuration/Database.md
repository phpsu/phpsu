# Configure Database

Suppose your application has a MySQL/MariaDB Database where your application state ist stored. 
Then we might need this Database for our local development, 
to find a bug better, or to view the design in its entirety.

For this we have included the Option to sync specific Databases:

````php
<?php
declare(strict_types=1);

$globalConfig = new PHPSu\Config\GlobalConfig;
$globalConfig->addDatabase('appDb', 'mysql://user:password@host/database_to_select');
````

With this phpsu will sync the complete contents of the Database `database_to_select` from the source ApplicationInstance to the destination.
Probably the connection to your database on the source system is different than on the target system.
For this you can overwrite the connection to your database on every `AppInstance`:

````php
<?php
declare(strict_types=1);

$globalConfig = new PHPSu\Config\GlobalConfig;
$globalConfig->addDatabase('appDb', 'mysql://user:password@host/database_to_select');
$globalConfig->addSshConnection('hostA', 'ssh://user@localhost:2208');
$appInstanceProduction = $globalConfig->addAppInstance('production', 'hostA', '/var/www/');
$appInstanceProduction->addDatabase('appDb', 'mysql://differentUser:differentPassword@host/different_database_to_select');
$globalConfig->addAppInstance('local');
````

With this Configuration the database `appDb` can be synchronised from **production** to **local**. As all necessary information is present.
This will sync the `different_database_to_select` from `hostA`'s connection `differentUser:differentPassword@host` to
`database_to_select` from `"local"`'s connection `user:password@host`.
 
