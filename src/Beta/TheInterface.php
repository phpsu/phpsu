<?php
declare(strict_types=1);

namespace PHPSu\Beta;

final class TheInterface
{
    /**
     * @param string[] $commands
     * @return void
     */
    public function execute(array $commands): void
    {
        $manager = new ProcessManager();
        foreach ($commands as $name => $command) {
            $process = Process::fromShellCommandline($command, null, null, null, null);
            $process->setName($name);
            $manager->addProcess($process);
        }
        $manager->mustRun();
    }
}
