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

    private function __construct()
    {
        $this->container = new Container();
        $this->container->delegate(new ReflectionContainer());
        $this->application = $this->container->get(Application::class);
    }

    private function registerCommands(): void
    {
        $this->application->add(new SyncCommand());
    }

    public function addDependency(string $className): self
    {
        $this->container->add($className);
        return $this;
    }

    /**
     * @param string $className
     * @return array|mixed|object
     */
    public function getDependency(string $className)
    {
        return $this->container->get($className);
    }

    public static function command(): self
    {
        $commandController = new self();
        $commandController->registerCommands();
        $commandController->application->run();
        return $commandController;
    }
}
