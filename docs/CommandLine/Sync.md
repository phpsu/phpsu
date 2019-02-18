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
They can not be the same as phpsu would then be jobless.

### The Option --dry-run

```bash
phpsu sync source destination --dry-run 
``` 

This option shows how the synchronisation is going to perform without actually synchronising anything.
`--dry-run` gives you the exact commands so you can check them.

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

The `--all` option lets you ignore the excludes of your configuration. 
It is going to sync all Files and all Database Tables.


### The Option --no-fs

```bash
phpsu sync source destination --no-fs
``` 

With the `--no-fs` option phpsu will ignore all `->addFilesystem()` configurations.

### The Option --no-db

```bash
phpsu sync source destination --no-db
``` 

With the `--no-db` option phpsu will ignore all `->addDatabase()` configurations.

### The Option --verbose

```bash
phpsu sync source destination --verbose
phpsu sync source destination -v
``` 

With the `--verbose` option you can see the output of the commands run by phpsu.
The level of verbosity can be change by applying more `-v` to the command.
Phpsu will gave the verbosity option to the underling commands as specified.
`-v`, `-vv`, `-vvv` and `-q` are possible. `-q` will remove all output that isn't error output.

### The Option --from

```bash
phpsu sync source destination --from=currentSystem
``` 

Phpsu tries to find out the shortest ssh connection.  
If we give phpsu the information on which system we are currently on,
it can shorten the connections and speed up syncing.
``--from`` accepts any sshConnection Host.  
> !! It is not recommended to install phpsu on a production system.


## Examples

### Sync From Production to Local

```bash
phpsu sync production local
phpsu sync production
``` 

You can skip local as it is the default for destination

### Sync From Production to Testing

```bash
phpsu sync production testing
``` 

This will sync the Production System to the Testing System.

### Being on Production and Syncing to Testing

```bash
phpsu sync production testing --from=production
``` 

If you are on the Production System and would like to sync to the Testing,
you can use the ``--from`` option to get the Ssh Configuration right.  
