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

    /**
     * @param InputInterface $input
     * @param string $argumentName
     * @return string|string[]|null
     */
    public function getArgument(InputInterface $input, string $argumentName)
    {
        return $input->getArgument($argumentName);
    }

    /**
     * @param InputInterface $input
     * @param string $argumentName
     * @return bool|string|string[]|null
     */
    public function getOption(InputInterface $input, string $argumentName)
    {
        return $input->getOption($argumentName);
    }
}
