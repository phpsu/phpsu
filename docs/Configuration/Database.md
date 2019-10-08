# Configure Database

Suppose your application has a MySQL/MariaDB Database where your application state ist stored. 
Then we might need this Database for our local development, 
to find a bug better, or to view the design in its entirety.

For this we have included the option to do exactly that:

````php
<?php
declare(strict_types=1);

$globalConfig = new PHPSu\Config\GlobalConfig;
$globalConfig->addDatabase('appDb', 'mysql://user:password@host/database_to_select');
````

The above configuration tells phpsu to sync every content of your database `database_to_select` from the source ApplicationInstance to the destination.
In the circumstance, that the database connection on your source system differs from the target system,
That's why you can overwrite the connection to your database for every `AppInstance`:

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

With this configuration the database `appDb` can be synchronised from **production** to **local** as all necessary information is present.
This will sync the `different_database_to_select` from `hostA`'s connection `differentUser:differentPassword@host` to
`database_to_select` from `"local"`'s connection `user:password@host`.
 
