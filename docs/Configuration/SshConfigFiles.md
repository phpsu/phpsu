# Using existing SSH config files

Additionally to `addSshConnection()` you can include existing ssh config files.  
All `Host`s defined in these files can be used like ssh connections, eg. as host of an AppInstance or with `--from`.

````php
<?php
declare(strict_types=1);

$globalConfig = new \PHPSu\Config\GlobalConfig();
// relative to the phpsu-config.php
$globalConfig->addSshConfigFile('ssh/config');
// absolute
$globalConfig->addSshConfigFile('/etc/company/ssh_config');
// relative to your home directory
$globalConfig->addSshConfigFile('~/.ssh/config_company');
// downloaded via https and cached for 600 seconds (default)
$globalConfig->addSshConfigFile('https://example.com/ssh_config');
// cached for one hour, with basic auth
$globalConfig->addSshConfigFile('https://example.com/private/ssh_config', 3600)
    ->setBasicAuth('user', (string)getenv('SSH_CONFIG_PASSWORD'));
// basic auth can also be part of the url
$globalConfig->addSshConfigFile('https://user:password@example.com/private/ssh_config');

// Host "production-server" is defined in one of the files above
$globalConfig->addAppInstance('production', 'production-server', '/var/www/');
return $globalConfig;
````

## How it works

The ssh config files are included into the generated `.phpsu/config/ssh_config` with `Include`.  
As ssh always uses the first obtained value, the order is:

1. the ssh connections added with `addSshConnection()` (they win if a Host is defined in both)
2. the ssh config files, in the order they were added
3. the default ssh config (`setDefaultSshConfig()`, `Host *`)

Hosts with wildcards (eg. `Host *.example.com`) still work within ssh, but phpsu only knows Hosts without wildcards.  
`Include`s inside local ssh config files are resolved by ssh, but phpsu does not know their Hosts.  
`Include` requires OpenSSH 7.3 or newer.

## Local files

- relative paths are resolved relative to the directory of the `phpsu-config.php`
- `~/` is resolved to your home directory
- if a local file does not exist phpsu stops with an error

## Downloaded files

- only `https://` urls are allowed (redirects to other protocols are refused, credentials are not sent to other hosts)
- downloaded files are cached in `.phpsu/ssh-configs/` (eg. `example.com-ssh-config-1a2b3c4d.conf`) for the given ttl in seconds (default 600 = 10 minutes)
- use the option `--refresh-ssh-config` to download all files again, ignoring the cache
- if the download fails phpsu warns and uses the cached version, even if it is expired.
  If there is no cached version the Hosts of this file are not available.

### Security

A ssh config file can execute commands on your machine. Therefore phpsu removes these options from downloaded files:

`ProxyCommand`, `LocalCommand`, `PermitLocalCommand`, `RemoteCommand`, `KnownHostsCommand`, `PKCS11Provider`, `SecurityKeyProvider`, `XAuthLocation`, `Include`
and complete `Match` blocks that use `exec`.

The removed lines are listed at the top of the cached file and shown with `-v`.  
Use `ProxyJump` instead of `ProxyCommand`.  
Local files are used as they are.
