<?php

declare(strict_types=1);

namespace PHPSu\Cli;

use PHPSu\Helper\ApplicationHelper;
use PHPSu\Config\ConfigurationLoader;
use PHPSu\Controller;
use Symfony\Component\Console\Application;

final class PhpsuApplication
{
    public static function createApplication(): Application
    {
        $application = new Application('phpsu', (new ApplicationHelper())->getCurrentPHPSUVersion());
        $configurationLoader = new ConfigurationLoader();
        $application->add(new SyncCliCommand($configurationLoader, new Controller()));
        $application->add(new SshCliCommand($configurationLoader, new Controller()));
        $application->add(new InfoCliCommand($configurationLoader, new Controller()));
        return $application;
    }
}
