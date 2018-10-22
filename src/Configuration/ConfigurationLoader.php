<?php
declare(strict_types=1);

namespace PHPSu\Configuration;

use PHPSu\Configuration\Loader\AbstractConfigurationLoader;
use PHPSu\Configuration\RawConfiguration\RawConfigurationDto;
use PHPSu\Core\ApplicationContextAwareTrait;

class ConfigurationLoader
{
    use ApplicationContextAwareTrait;

    public function getRawConfig(): RawConfigurationDto
    {
        $loaderClass = $this->context->configurationLoaderEnum;
        if (!class_exists($loaderClass)) {
            throw new \RuntimeException('loaderClass ' . $loaderClass . ' dose not exists');
        }
        if (!is_subclass_of($loaderClass, AbstractConfigurationLoader::class)) {
            throw new \RuntimeException('loaderClass ' . $loaderClass . ' dose not implement ' . AbstractConfigurationLoader::class);
        }
        /** @var AbstractConfigurationLoader $loader */
        $loader = new $loaderClass();
        return $loader->getRawConfiguration();
    }
}
