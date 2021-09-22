<?php

declare(strict_types=1);

namespace PHPSu;

use DI\Container;
use DI\ContainerBuilder;
use PHPSu\Config\GlobalConfig;
use Symfony\Component\Console\Application;

/**
 * The bootstrapping class initiating the dependency injection container and preparing all commands as well as the configuration
 * @internal
 */
final class Kernel
{
    protected static ?Container $container = null;

    public function __construct()
    {
        $builder = new ContainerBuilder();
        $builder->addDefinitions(__DIR__ . '/services.php');
        $builder->useAnnotations(false);
        self::$container = $builder->build();
    }

    public static function getContainer(): Container
    {
        if (!self::$container) {
            new self();
        }
        assert(self::$container instanceof Container);
        return self::$container;
    }

    public static function run(): int
    {
        return self::getContainer()->get(Application::class)->run();
    }

    public static function config(): GlobalConfig
    {
        return self::getContainer()->get(GlobalConfig::class);
    }
}
