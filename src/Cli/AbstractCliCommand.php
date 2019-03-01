<?php
declare(strict_types=1);


namespace PHPSu\Cli;

use PHPSu\Config\ConfigurationLoaderInterface;
use PHPSu\ControllerInterface;
use Symfony\Component\Console\Command\Command;

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
}
