<?php
declare(strict_types=1);

namespace PHPSu\Core;

use PHPSu\Configuration\Enum\ConfigurationLoaderEnum;

class ApplicationContext
{
    /**
     * @var ConfigurationLoaderEnum
     */
    public $configurationLoaderEnum;

    public function __construct()
    {
        $this->configurationLoaderEnum = new ConfigurationLoaderEnum();
    }
}
