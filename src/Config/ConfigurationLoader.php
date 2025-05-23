<?php

declare(strict_types=1);

namespace PHPSu\Config;

use RuntimeException;

/**
 * @internal
 */
final class ConfigurationLoader implements ConfigurationLoaderInterface
{
    private ?GlobalConfig $config = null;

    public function getConfig(): GlobalConfig
    {
        if ($this->config === null) {
            $file = getcwd() . '/phpsu-config.php';
            if (!file_exists($file)) {
                throw new RuntimeException(sprintf('%s does not exist', $file));
            }

            $config = require $file;
            if (!$config instanceof GlobalConfig) {
                throw new RuntimeException(sprintf('Invalid config file %s (it needs to return a ' . GlobalConfig::class . ' class)', $file));
            }

            $this->config = $config;
        }

        return $this->config;
    }
}
