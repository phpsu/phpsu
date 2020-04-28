# Configure Database

If you have a MySQL/MariaDB database for your application and you need to debug a bug, occurring on a specific system or you have to analyze its entire structure or you just want to copy a production environment for local development, then you need to be able to sync specific databases.

For this we have included the option to do exactly that:

````php
<?php
declare(strict_types=1);

$globalConfig = new PHPSu\Config\GlobalConfig;
$globalConfig->addDatabaseByUrl('appDb', 'mysql://user:password@host/database_to_select');
````

The above configuration tells phpsu to sync every content of your database `database_to_select` from the source ApplicationInstance to the destination.
In the circumstance, that the database connection on your source system differs from the target system,
That's why you can overwrite the connection to your database for every `AppInstance`:

````php
<?php
declare(strict_types=1);

$globalConfig = new PHPSu\Config\GlobalConfig;
$globalConfig->addDatabaseByUrl('appDb', 'mysql://user:password@host/database_to_select');
$globalConfig->addSshConnection('hostA', 'ssh://user@localhost:2208');
$appInstanceProduction = $globalConfig->addAppInstance('production', 'hostA', '/var/www/');
$appInstanceProduction->addDatabaseByUrl('appDb', 'mysql://differentUser:differentPassword@host/different_database_to_select');
$globalConfig->addAppInstance('local');
````

With this configuration the database `appDb` can be synchronised from **production** to **local** as all necessary information is present.
This will sync the `different_database_to_select` from `hostA` to `database_to_select` from `"local"` connection `user:password@host`. The database connection of `hostA` is has the configuration string `differentUser:differentPassword@host` while "local" uses `user:password@host` to connect.
 
