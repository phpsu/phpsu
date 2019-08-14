# Configure Filesystem

Suppose your application has a directory where uploaded profiles store images. 
Then we might need them for locale development. 
To find a bug better, or to view the design in its entirety.

For this we have included the Option to sync specific Directories:

````php
<?php
declare(strict_types=1);

$globalConfig = new PHPSu\Config\GlobalConfig;
$globalConfig->addFilesystem('profilePictures', 'relativePath/toProfile/Pictures');
````

With this phpsu will sync the Complete contents of the Directory `relativePath/toProfile/Pictures` from the source ApplicationInstance to the destination.
If your Source is located at `/var/www` on `hostAlbert` and your destination is at `/home/web/www` on `hostBerta`:
 
The Filesystem `profilePictures` will sync the absolute directory `/var/www/relativePath/toProfile/Pictures/` from `hostAlbert`
 with all its content into `/home/web/www/relativePath/toProfile/Pictures/` on `hostBerta`.

## Excluding Filesystem Elements

