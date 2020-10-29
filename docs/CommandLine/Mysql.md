# phpsu mysql

This is the basic command that provides the ability to connect to a remote database.

## Usage

```bash

Usage:
  mysql [options] [--] <instance> [<mysqlcommand>]

Arguments:
  instance                 Which AppInstance to connect to
  mysqlcommand             Execute a mysql command instead of connecting to it

Options:
  -d, --dry-run            just display the commands.
  -b, --database=DATABASE  Which Database to connect to
  -h, --help               Display this help message
  -q, --quiet              Do not output any message
  -V, --version            Display this application version
      --ansi               Force ANSI output
      --no-ansi            Disable ANSI output
  -n, --no-interaction     Do not ask any interactive question
  -v|vv|vvv, --verbose     Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug

```

```shell script

phpsu mysql production
# creates a connection database of production

```

If you either don't define the database or the instance and there are more than one of each, then a quick guide asks you to fill those blanks.

```php
<?php
// phpsu-config.php
$config = new \PHPSu\Config\GlobalConfig();
$config->addSshConnection('ThisHost', '...');
$config->addAppInstance('ThisAppInstance', '...')
    ->addDatabase('base', '...');
// ...
```

The keyword `instance` can either be replaced with an AppInstanceName or a HostName. 
Keeping that in mind, with the example config from above, the destination can either be ``ThisHost``  or ``ThisAppInstance``.
If an AppInstance is being used as the destination, the configured path will be the working directory right after connecting.

### Providing a <mysqlcommand>

```bash
phpsu mysql production --database=base "SELECT * FROM TABLE WHERE 1 != 1"
```

### The Option --dry-run

```bash
phpsu mysql production --database=base --dry-run
``` 

This option shows how the synchronisation is going to perform without actually running the command.  

> Note:
> This command assumes you always want to connect from your local standpoint
