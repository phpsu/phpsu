# phpsu sync

This command is the basic command that provides the entire suit of synchronising data between two systems

## Usage

```bash
phpsu sync --help
phpsu sync -h
```  

This option shows all possible options and arguments and gives hints about the usages of the sub-commands.

### The Arguments source and destination

```bash
phpsu sync source destination
```

```php
<?php
// phpsu-config.php
$config = new \PHPSu\Config\GlobalConfig();
$config->addAppInstance('production', '...');
$config->addAppInstance('testing', '...');
$config->addAppInstance('local', '...');
// ...
```

The Arguments can be any of the defined AppInstances. 
They can not be the same AppInstance as than phpsu would be jobless.

### The Option --dry-run

```bash
phpsu sync source destination --dry-run 
``` 

This option shows how the synchronisation is going to perform without actually synchronising anything.
It will show you the commands that would run without the `--dry-run` option

### The Option --all

```bash
phpsu sync source destination --all
phpsu sync source destination -a
``` 

```php
<?php
// phpsu-config.php
$config = new \PHPSu\Config\GlobalConfig();
$config->addDatabase('...')->addExclude('table1');
$config->addFilesystem('...')->addExclude('*.mp4');
// ...
```

The `--all` option lets you ignore the excludes of your Configuration. 
It will sync all Files and all Database Tables.


### The Option --no-file

```bash
phpsu sync source destination --no-file
``` 

With the `--no-file` option phpsu will ignore all `->addFilesystem()` Configurations.

### The Option --no-db

```bash
phpsu sync source destination --no-db
``` 

With the `--no-db` option phpsu will ignore all `->addDatabase()` Configurations.

### The Option --verbose

```bash
phpsu sync source destination --verbose
phpsu sync source destination -v
``` 

[//] todo add description  

### The Option --from

```bash
phpsu sync source destination --from=currentSystem
``` 

[//] todo add description  



## Examples

### Sync From Production to Local

```bash
phpsu sync production local
``` 

[//] todo add description  

### Sync From Production to Testing

```bash
phpsu sync production testing
``` 

[//] todo add description  

### Being on Production and Syncing to Testing

```bash
phpsu sync production testing --from=production
``` 

[//] todo add description  
