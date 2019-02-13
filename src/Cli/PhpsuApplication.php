<?php
declare(strict_types=1);

namespace PHPSu\Cli;

use PHPSu\Exceptions\EnvironmentException;
use PHPSu\Helper\InternalHelper;
use PHPSu\Tools\EnvironmentUtility;
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
        if ((new EnvironmentUtility())->isWindows()) {
            throw new EnvironmentException('We currently do not support windows');
        }
        $application = new Application('phpsu', (new InternalHelper())->getCurrentPHPSUVersion());
        $configurationLoader = new ConfigurationLoader();
        $application->add(new SyncCliCommand($configurationLoader, new Controller()));
        $application->add(new SshCliCommand($configurationLoader, new Controller()));
        return $application;
    }
}
