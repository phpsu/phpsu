<?php
declare(strict_types=1);


namespace PHPSu\Cli;

use PHPSu\Config\ConfigurationLoaderInterface;
use PHPSu\ControllerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;

abstract class AbstractCliCommand extends Command
{
    /** @var ConfigurationLoaderInterface */
    protected $configurationLoader;
    /** @var ControllerInterface */
    protected $controller;

    public function __construct(ConfigurationLoaderInterface $configurationLoader, ControllerInterface $controller)
    {
        parent::__construct();
        $this->configurationLoader = $configurationLoader;
        $this->controller = $controller;
    }

    public function getArgument(InputInterface $input, string $argumentName)
    {
        return $input->getArgument($argumentName);
    }

    public function getOption(InputInterface $input, string $argumentName)
    {
        return $input->getOption($argumentName);
    }
}
