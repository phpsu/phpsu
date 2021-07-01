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
    /** @var GlobalConfig */
    protected $config;
    /** @var ControllerInterface */
    protected $controller;

    public function __construct(GlobalConfig $config, ControllerInterface $controller)
    {
        parent::__construct();
        $this->config = $config;
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
