# Phpsu (Synchronisation Utility: File and Database)

[![Latest Version](https://img.shields.io/github/release/phpsu/phpsu.svg?style=flat-square)](https://github.com/phpsu/phpsu/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)
[![Build Status](https://img.shields.io/travis/phpsu/phpsu/master.svg?style=flat-square)](https://travis-ci.org/phpsu/phpsu)
[![Coverage Status](https://img.shields.io/scrutinizer/coverage/g/phpsu/phpsu.svg?style=flat-square)](https://scrutinizer-ci.com/g/phpsu/phpsu/code-structure)
[![Quality Score](https://img.shields.io/scrutinizer/g/phpsu/phpsu.svg?style=flat-square)](https://scrutinizer-ci.com/g/phpsu/phpsu)
[![Total Downloads](https://img.shields.io/packagist/dt/league/container.svg?style=flat-square)](https://packagist.org/packages/phpsu/phpsu)

This package is compliant with [PSR-1], [PSR-2] and [PSR-4]. If you notice compliance oversights, please send a patch via pull request.

[PSR-1]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md
[PSR-2]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md
[PSR-4]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md

## Install

Via Composer

`` bash
composer require phpsu/phpsu
``

## Requirements

The following versions of PHP are supported by this version.

* PHP 7.1
* PHP 7.2
* PHP 7.3

Required for synchronisation are:
* ``ssh`` on execution System
* ``rsync`` on executing System
* ``mysqldump`` and ``mysql`` on source and destination Systems

## Documentation

## Testing

````bash
composer test
````

## Contributing

## Security

If you discover any security related issues, please email git@kanti.de instead of using the issue tracker.

## Credits

- [Matthias Vogel](https://github.com/Kanti)
- [Chris Ben](https://github.com/ChrisB9)
- [All Contributors](https://github.com/phpsu/phpsu/contributors)

## License

The MIT License (MIT). Please see [License File](https://github.com/phpsu/phpsu/blob/master/LICENSE) for more information.
