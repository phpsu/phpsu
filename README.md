# Phpsu: Synchronisation Utility: File and Database

[![Latest Version](https://img.shields.io/github/release-pre/phpsu/phpsu.svg?style=flat-square)](https://github.com/phpsu/phpsu/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)
[![Build Status](https://img.shields.io/travis/phpsu/phpsu/master.svg?style=flat-square)](https://travis-ci.org/phpsu/phpsu)
[![Coverage Status](https://img.shields.io/codecov/c/gh/phpsu/phpsu.svg?style=flat-square)](https://codecov.io/gh/phpsu/phpsu)
[![Quality Score](https://img.shields.io/scrutinizer/g/phpsu/phpsu.svg?style=flat-square)](https://scrutinizer-ci.com/g/phpsu/phpsu)
[![Total Downloads](https://img.shields.io/packagist/dt/phpsu/phpsu.svg?style=flat-square)](https://packagist.org/packages/phpsu/phpsu)

This package is compliant with [PSR-1], [PSR-2] and [PSR-4]. If you notice compliance oversights, please send a patch via pull request.

[PSR-1]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md
[PSR-2]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md
[PSR-4]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md

## Install

Via Composer:

````bash
composer require --dev phpsu/phpsu
````

### Install with conflicting versions

If you have problems with conflicting versions. eg. symfony:2.* you can use the [composer-bin-plugin]

````bash
composer require --dev bamarni/composer-bin-plugin
composer bin phpsu require --dev phpsu/phpsu
#  we recommend to install it with the auto installation scripts:
composer config scripts.bin "echo 'bin not installed'"
composer config scripts.post-install-cmd '@composer bin all install --ansi'
composer config scripts.post-update-cmd '@composer bin all update --ansi'
echo '/vendor-bin/**/vendor' >> .gitignore
````

[composer-bin-plugin]: https://github.com/bamarni/composer-bin-plugin

## Requirements

The following versions of PHP are supported by this version.

* PHP `7.0`, `7.1`, `7.2` and `7.3`
* Compatible and continuously tested with symfony `3.2`, `3.4` and `4.3`

Required for synchronisation are:
* ``ssh`` on execution System
* ``rsync`` on executing System
* ``mysqldump`` on source System
* ``mysql`` on destination Systems

Unfortunately we do not support Windows yet.

## Documentation

The full [Documentation](docs/index.md) can be found in the ``/docs`` Directory.

## Configuration Example

Simple configuration example `phpsu-config.php`:

````php
<?php
declare(strict_types=1);

$config = new \PHPSu\Config\GlobalConfig();
$config->addFilesystem('Image Uploads', 'var/storage')
    ->addExclude('*.mp4')
    ->addExclude('*.mp3')
    ->addExclude('*.zip')
    ->addExcludes(['*.jpg', '*.gif']);
$config->addSshConnection('hostA', 'ssh://user@localhost:2208');
$config->addAppInstance('production', 'hostA', '/var/www/')
    ->addDatabase('app', 'mysql://root:password@127.0.0.1:3307/production01db')
    ->addExclude('/cache/')
    ->addExclude('/session$/')
    ->addExcludes(['/log/']);
$config->addAppInstance('local')
    ->addDatabase('app', 'mysql://root:root@127.0.0.1/testingLocal');
return $config;
````

## CLI Examples

````bash
phpsu sync production --dry-run
phpsu sync p --no-db
phpsu sync p --no-fs
phpsu sync production testing --all
````

<!--### PHP API Examples

````php
<?php
declare(strict_types=1);

$log = new \Symfony\Component\Console\Output\BufferedOutput();
$configurationLoader = new \PHPSu\Config\ConfigurationLoader();
$syncOptions = new \PHPSu\SyncOptions('production');
$phpsu = new \PHPSu\Controller();
$phpsu->sync($log, $configurationLoader->getConfig(), $syncOptions);
````-->

## Contributing

install for Contribution
````bash
git clone git@github.com:phpsu/phpsu.git
cd phpsu
composer install
````

## Testing

````bash
composer test
````

You can also check, whether any changes you made are affecting your tests immediatly on save:
````bash
composer test:watch
````

If you see a low `Mutation Score Indicator (MSI)` value, you can show the mutations that are escaping:
````bash
composer infection -- -s
````

## Security

If you discover any security related issues, please email git@kanti.de instead of using the issue tracker.

## Credits

- [Matthias Vogel](https://github.com/Kanti)
- [Chris Ben](https://github.com/ChrisB9)
- [All Contributors](https://github.com/phpsu/phpsu/contributors)

## License

The MIT License (MIT). Please see [License File](https://github.com/phpsu/phpsu/blob/master/LICENSE) for more information.
