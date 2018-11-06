<?php
declare(strict_types=1);

namespace PHPSu;

use League\Container\Container;
use League\Container\ReflectionContainer;
use PHPSu\Console\SyncCommand;
use Symfony\Component\Console\Application;

final class CommandController
{
    /**
     * @var Container
     */
    private $container;
    /**
     * @var Application
     */
    private $application;

    /**
     * Kernel constructor.
     * @throws \Exception
     */
    private function __construct()
    {
        $this->container = new Container();
        $this->container->delegate(new ReflectionContainer());
        $this->application = $this->container->get(Application::class);
    }

    /**
     * @throws \Exception
     */
    private function registerCommands()
    {
        $this->application->add(new SyncCommand());
    }

    public static function command()
    {
        $commandController = new self();
        $commandController->registerCommands();
        $commandController->application->run();
    }
}
