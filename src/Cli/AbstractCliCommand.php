<?php

declare(strict_types=1);

namespace PHPSu\Cli;

use PHPSu\Config\GlobalConfig;
use PHPSu\ControllerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;

/**
 * @internal
 */
abstract class AbstractCliCommand extends Command
{
    protected GlobalConfig $config;
    protected ControllerInterface $controller;

    public function __construct(GlobalConfig $config, ControllerInterface $controller)
    {
        parent::__construct();
        $this->config = $config;
        $this->controller = $controller;
    }
}
