<?php

declare(strict_types=1);

namespace PHPSu\Cli;

use PHPSu\Config\ConfigurationLoaderInterface;
use PHPSu\ControllerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;

/**
 * @internal
 */
abstract class AbstractCliCommand extends Command
{
    protected ConfigurationLoaderInterface $configurationLoader;
    protected ControllerInterface $controller;

    public function __construct(ConfigurationLoaderInterface $configurationLoader, ControllerInterface $controller)
    {
        parent::__construct();
        $this->configurationLoader = $configurationLoader;
        $this->controller = $controller;
    }
}
