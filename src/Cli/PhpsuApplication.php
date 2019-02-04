<?php
declare(strict_types=1);

namespace PHPSu\Cli;

use PHPSu\Exceptions\EnvironmentException;
use PHPSu\Tools\EnvironmentUtility;
use PHPSu\Tools\InternalHelpers;
use Symfony\Component\Console\Application;

final class PhpsuApplication
{
    public static function command(): void
    {
        if ((new EnvironmentUtility())->isWindows()) {
            throw new EnvironmentException('We currently do not support windows');
        }
        $application = new Application('phpsu', (new InternalHelpers())->getCurrentPHPSUVersion());
        $command = new SyncCliCommand();
        $application->add($command);
        $application->add(new SshCliCommand());
        $application->run();
    }
}
