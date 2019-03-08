<?php
declare(strict_types=1);

namespace PHPSu\Config;

final class ConfigurationLoader implements ConfigurationLoaderInterface
{
    /** @var ?GlobalConfig */
    private $config;

    public function getConfig(): GlobalConfig
    {
        if (!$this->config) {
            $this->config = require getcwd() . '/phpsu-config.php';
        }
        return $this->config;
    }
}
