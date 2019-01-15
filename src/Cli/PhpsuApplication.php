<?php
declare(strict_types=1);

namespace PHPSu\Cli;

use Symfony\Component\Console\Application;

final class PhpsuApplication
{
    public static function command(): void
    {
        $application = new Application('phpsu', '1.0.0-dev');
        $command = new SyncCliCommand();
        $application->add($command);
        $application->add(new SshCliCommand());
        $application->run();
    }
}
