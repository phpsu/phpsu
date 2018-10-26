<?php
declare(strict_types=1);

namespace PHPSu\Configuration;

use League\Container\Container;
use PHPSu\Configuration\Loader\AbstractConfigurationLoader;
use PHPSu\Configuration\RawConfiguration\RawConfigurationDto;
use PHPSu\Core\ApplicationContext;

class ConfigurationLoader
{
    /**
     * @var Container
     */
    private $container;

    /**
     * @var ApplicationContext
     */
    private $context;

    public function __construct(Container $container, ApplicationContext $context)
    {
        $this->container = $container;
        $this->context = $context;
    }

    public function getRawConfig(): RawConfigurationDto
    {
        $loaderClass = $this->context->configurationLoaderEnum;
        if (!class_exists($loaderClass)) {
            throw new \RuntimeException('loaderClass ' . $loaderClass . ' dose not exists');
        }
        if (!is_subclass_of($loaderClass, AbstractConfigurationLoader::class)) {
            throw new \RuntimeException('loaderClass ' . $loaderClass . ' dose not implement ' . AbstractConfigurationLoader::class);
        }
        if (!$this->container->has($loaderClass)) {
            throw new \RuntimeException('Container could not locate Class ' . $loaderClass);
        }
        $loader = $this->container->get($loaderClass, false);
        return $loader->getRawConfiguration();
    }
}
