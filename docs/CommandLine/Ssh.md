# phpsu ssh

This is the basic command that provides the ability to connect to a remote system.

## Usage

```bash
phpsu ssh --help
phpsu ssh -h
```

This option gives you a quick overview on how to use it.

### The Argument destination

```bash
phpsu ssh destination
```

```php
<?php
// phpsu-config.php
$config = new \PHPSu\Config\GlobalConfig();
$config->addSshConnection('ThisHost', '...');
$config->addAppInstance('ThisAppInstance', '...');
// ...
```

The keyword destination can either be replaced with an AppInstance Name or a HostName. 
Keeping that in mind, with the example config from above, the destination can either be ``ThisHost``  or ``ThisAppInstance``.
If an AppInstance is being used as the destination, the configured path will be the working directory right after connecting

### The Argument commands

```bash
phpsu ssh destination [...commands]
```

The Argument commands can be an arbitrary number of commands that will be executed on the remote server.
The program will not hold after the execution of the commands. 
Keep in mind that the working directory depends on the destination. (see [The Argument destination](Ssh.md#the-argument-destination))

### The Option --dry-run

```bash
phpsu ssh destination --dry-run
``` 

This option shows how the synchronisation is going to perform without actually running the command.  

### The Option --from

```bash
phpsu ssh destination --from=currentSystem
``` 

This option can be used to shorten the connection length for ssh connections.
You need to set this option if the destination system is only accessible by a Proxy
and you are on one of the Proxy Servers. (more on this see [Connecting to a Server over Proxies](../index.md#TODO)) 
