<?php
declare(strict_types=1);

namespace PHPSu\Cli;

use PHPSu\Config\ConfigurationLoader;
use PHPSu\Controller;
use Symfony\Component\Console\Application;

final class PhpsuApplication
{
    public static function command(): void
    {
        self::createApplication()->run();
    }

    public static function createApplication(): Application
    {
        $application = new Application('phpsu', '1.0.0-alpha3');
        $configurationLoader = new ConfigurationLoader();
        $application->add(new SyncCliCommand($configurationLoader, new Controller()));
        $application->add(new SshCliCommand($configurationLoader, new Controller()));
        return $application;
    }
}
