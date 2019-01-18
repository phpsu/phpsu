<?php
declare(strict_types=1);

namespace PHPSu\Cli;

use PHPSu\Tools\EnvironmentUtility;
use PHPSu\Tools\InternalHelpers;
use Symfony\Component\Console\Application;

final class PhpsuApplication
{
    public static function command(): void
    {
        if (EnvironmentUtility::isWindows()) {
            throw new \RuntimeException('We currently do not support windows');
        }

        $version = (new InternalHelpers())->getCurrentPHPSUVersion();
        $application = new Application('phpsu', $version);
        $command = new SyncCliCommand();
        $application->add($command);
        $application->add(new SshCliCommand());
        $application->run();
    }
}
