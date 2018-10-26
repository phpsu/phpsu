<?php
declare(strict_types=1);

namespace PHPSu\Configuration\Enum;

use PHPSu\Configuration\Loader\XmlConfigurationLoader;

final class ConfigurationLoaderEnum
{

    protected $value;

    public function __construct($value = null)
    {
        $this->value = $value ?? self::__DEFAULT;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    const __DEFAULT = self::XML;

    const XML = XmlConfigurationLoader::class;
//    const JSON = 'json';
//    const ENV = 'env';
//    const DOT_ENV = '.env';
}
