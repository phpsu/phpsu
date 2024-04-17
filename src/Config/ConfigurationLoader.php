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

            $this->config = require $file;
        }

        return $this->config;
    }
}
