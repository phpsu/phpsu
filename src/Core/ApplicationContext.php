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

    /**
     * @var string
     */
    public $fromHost;

    /**
     * @var string
     */
    public $toHost;

    public function __construct()
    {
        $this->configurationLoaderEnum = new ConfigurationLoaderEnum();
    }
}
