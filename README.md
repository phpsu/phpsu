# Phpsu: Synchronisation Utility: File and Database

[![Latest Version](https://img.shields.io/github/release-pre/phpsu/phpsu.svg?style=flat-square)](https://github.com/phpsu/phpsu/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)
[![Coverage Status](https://img.shields.io/codecov/c/gh/phpsu/phpsu.svg?style=flat-square)](https://codecov.io/gh/phpsu/phpsu)
[![Infection MSI](https://img.shields.io/endpoint?style=flat-square&url=https://badge-api.stryker-mutator.io/github.com/phpsu/phpsu/master)](https://infection.github.io)
[![Total Downloads](https://img.shields.io/packagist/dt/phpsu/phpsu.svg?style=flat-square)](https://packagist.org/packages/phpsu/phpsu)

This package is compliant with [PSR-1], [PSR-2], [PSR-4] and [PSR-12]. If you notice compliance oversights, please send a patch via pull request.

[PSR-1]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md
[PSR-2]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md
[PSR-4]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md
[PSR-12]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-12-extended-coding-style-guide.md

## Install

### Via Composer:

````bash
composer require --dev phpsu/phpsu
````

#### Via Composer, with conflicting versions

If you have problems with conflicting versions eg. symfony:<5 you can use the [composer-bin-plugin].

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

### Via Docker:

if you want to use phpsu via Docker we have a minimal phpsu docker image: [phpsu/phpsu].

you can execute any phpsu command via something like this:

``docker run --rm -it -u $(id -u):$(id -g) -v $(pwd):/app -v ~/.ssh:/home/phpsu/.ssh phpsu/phpsu:latest phpsu ssh production``

[read more about docker usage]


[phpsu/phpsu]: https://hub.docker.com/r/phpsu/phpsu
[read more about docker usage]: docs/Docker.md

## Requirements

The following versions of PHP are supported by this version.

* PHP `8.1`, `8.2`, `8.3`
* Compatible and continuously tested with symfony `5`, `6`, `7`
* for older versions go to [version 3.1.0](https://github.com/phpsu/phpsu/tree/3.1.0)
* for older versions go to [version 2.3.0](https://github.com/phpsu/phpsu/tree/2.3.0)
* or [version 1.1.0](https://github.com/phpsu/phpsu/tree/1.1.0)

Required for synchronisation are:
* ``bash`` on execution System (and the ssh user needs to have a shell where `set -o pipefail` is possible (eg. not sh on debian as that is dash and does not work))
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

$globalConfig = new \PHPSu\Config\GlobalConfig();
$globalConfig->addFilesystem('Image Uploads', 'var/storage')
    ->addExclude('*.mp4')
    ->addExclude('*.mp3')
    ->addExclude('*.zip')
    ->addExcludes(['*.jpg', '*.gif']);
$globalConfig->addSshConnection('hostA', 'ssh://user@localhost:2208');
$globalConfig->addAppInstance('production', 'hostA', '/var/www/')
    ->addDatabase('app', 'production01db', 'root', 'password', '127.0.0.1', 3307)
    ->addExclude('one_single_table_name')
    ->addExclude('/cache/')
    ->addExclude('/session$/')
    ->addExcludes(['/log/']);
$globalConfig->addAppInstance('local')
    ->addDatabase('app', 'testingLocal', 'root', 'root');
return $globalConfig;
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

You can also check, whether any changes you made are affecting your tests immediately on save:
````bash
composer test:watch
````

If you see a low `Mutation Score Indicator (MSI)` value, you can show the mutations that are escaping:
````bash
composer infection -- -s
````

If you have an unwanted BC break in your Pull Request you can run the same test locally with docker:
````bash
docker run --rm -v `pwd`:/app nyholm/roave-bc-check --format=markdown > results.md
````

## Security

If you discover any security related issues, please email git@kanti.de instead of using the issue tracker.

## Credits

- [Matthias Vogel](https://github.com/Kanti)
- [Christian Rodriguez Benthake](https://github.com/ChrisB9)
- [All Contributors](https://github.com/phpsu/phpsu/contributors)

## License

The MIT License (MIT). Please see [License File](https://github.com/phpsu/phpsu/blob/master/LICENSE) for more information.
