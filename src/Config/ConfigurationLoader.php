<?php

declare(strict_types=1);

namespace PHPSu\Config;

use RuntimeException;

/**
 * @internal
 */
final class ConfigurationLoader implements ConfigurationLoaderInterface
{
    /** @var ?GlobalConfig */
    private $config;

    /** @var string */
    private $configFile;

    public function __construct(string $configFile = 'phpsu-config.php')
    {
        $this->configFile = $configFile;
    }

    public function getConfig(): GlobalConfig
    {
        if ($this->config === null) {
            $file = getcwd() . '/' . $this->configFile;
            if (!file_exists($file)) {
                throw new RuntimeException(sprintf('%s does not exist', $file));
            }
            $this->config = require $file;
        }
        return $this->config;
    }
}
