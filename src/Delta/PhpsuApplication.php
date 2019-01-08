<?php
declare(strict_types=1);

namespace PHPSu\Delta;

use Symfony\Component\Console\Application;

class PhpsuApplication
{
    public static function command()
    {
        $application = new Application('phpsu', '1.0.0-dev');
        $command = new SyncCommand();
        $application->add($command);
        $application->add(new SshCommand());
//        $application->setDefaultCommand($command->getName());
        $application->run();
    }
}
