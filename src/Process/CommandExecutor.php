<?php

declare(strict_types=1);

namespace PHPSu\Process;

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
            $process = Process::fromShellCommandline($command, null, null, null, null);
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
     */
    public function passthru($command, OutputInterface $output): int
    {
        $process = Process::fromShellCommandline((string)$command);
        $process->setTimeout(null);
        $process->setTty($output->isDecorated() && \Symfony\Component\Process\Process::isTtySupported());

        $errorOutput = $output;
        if ($output instanceof ConsoleOutputInterface) {
            $errorOutput = $output->getErrorOutput();
        }

        return $process->run(static function ($type, $buffer) use ($output, $errorOutput): void {
            if ($type == \Symfony\Component\Process\Process::OUT) {
                $output->write($buffer);
            } else {
                $errorOutput->write($buffer);
            }
        });
    }

    /**
     * @return Process<mixed>
     * @throws ShellBuilderException
     */
    public function runCommand(ShellInterface $command): Process
    {
        $process = new Process(Pattern::split((string)$command));
        $process->setTimeout(null);
        $process->run();
        return $process;
    }
}
