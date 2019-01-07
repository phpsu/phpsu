<?php
declare(strict_types=1);

namespace PHPSu\Beta;

use Symfony\Component\Console\Output\OutputInterface;

final class TheInterface
{

    /**
     * @param string[] $commands
     * @param OutputInterface $logOutput
     * @param OutputInterface $statusOutput
     * @return void
     */
    public function execute(array $commands, OutputInterface $logOutput, OutputInterface $statusOutput): void
    {
        $manager = new ProcessManager();
        foreach ($commands as $name => $command) {
            $logOutput->writeln(sprintf('<fg=yellow>%s:</> <fg=white;options=bold>running command: %s</>', $name, $command), OutputInterface::VERBOSITY_VERBOSE);
            $process = Process::fromShellCommandline($command, null, null, null, null);
            $process->setName($name);
            $manager->addProcess($process);
        }
        $manager->addStateChangeCallback(new StateChangeCallback($statusOutput));
        $manager->addOutputCallback(new OutputCallback($logOutput));
        $manager->mustRun();
    }
}
