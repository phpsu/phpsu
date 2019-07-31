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
            $file = getcwd() . '/phpsu-config.php';
            if (!file_exists($file)) {
                throw new \RuntimeException(sprintf("%s does not exist", $file));
            }
            $this->config = require $file;
        }
        return $this->config;
    }
}
