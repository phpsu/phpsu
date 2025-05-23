<?php

declare(strict_types=1);

namespace PHPSu\Process;

use RuntimeException;
use PHPSu\ShellCommandBuilder\Definition\Pattern;
use PHPSu\ShellCommandBuilder\Exception\ShellBuilderException;
use PHPSu\ShellCommandBuilder\ShellInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @internal
 */
class CommandExecutor
{
    /**
     * @param string[] $commands
     */
    public function executeParallel(array $commands, OutputInterface $logOutput, OutputInterface $statusOutput): void
    {
        $manager = new ProcessManager();
        foreach ($commands as $name => $command) {
            $logOutput->writeln(sprintf('<fg=yellow>%s:</> <fg=white;options=bold>running command: %s</>', $name, $command), OutputInterface::VERBOSITY_VERBOSE);
            $process = new Process(['bash', '-c', $command], null, null, null, null);
            $process->setName($name);
            $manager->addProcess($process);
        }

        $callback = new StateChangeCallback($statusOutput);
        $manager->addStateChangeCallback($callback);
        $manager->addTickCallback($callback->getTickCallback());
        $manager->addOutputCallback(new OutputCallback($logOutput));
        $manager->mustRun();
    }

    /**
     * @param ShellInterface|string $command
     * @param resource|null $stdin
     * @param resource|null $stdout
     * @param resource|null $stderr
     */
    public function passthru($command, $stdin = null, $stdout = null, $stderr = null): int
    {
        $process = proc_open((string)$command, [$stdin ?: STDIN, $stdout ?: STDOUT, $stderr ?: STDERR], $_);
        if (!is_resource($process)) {
            throw new RuntimeException('Could not open process');
        }

        return proc_close($process);
    }
}
