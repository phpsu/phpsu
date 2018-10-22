<?php
declare(strict_types=1);

namespace PHPSu\Configuration\Enum;

use PHPSu\Configuration\Loader\XmlConfigurationLoader;

final class ConfigurationLoaderEnum extends \SplEnum
{
    const __DEFAULT = self::XML;

    const XML = XmlConfigurationLoader::class;
//    const JSON = 'json';
//    const ENV = 'env';
//    const DOT_ENV = '.env';
}
