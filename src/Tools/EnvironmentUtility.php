<?php

declare(strict_types=1);

namespace PHPSu\Tools;

use PHPSu\Command\CommandGenerator;
use PHPSu\Exceptions\CommandExecutionException;
use PHPSu\Process\CommandExecutor;
use PHPSu\Process\Process;
use PHPSu\ShellCommandBuilder\ShellBuilder;
use PHPSu\ShellCommandBuilder\ShellInterface;

/**
 * @internal
 */
final class EnvironmentUtility
{
    private CommandGenerator $commandGenerator;
    private CommandExecutor $commandExecutor;

    public function __construct(CommandGenerator $commandGenerator, CommandExecutor $executor)
    {
        $this->commandGenerator = $commandGenerator;
        $this->commandExecutor = $executor;
    }

    public function isRsyncInstalled(string $destination = '', string $current = ''): bool
    {
        return $this->isCommandInstalled('rsync', $destination, $current);
    }

    public function isMysqlDumpInstalled(string $destination = '', string $current = ''): bool
    {
        return $this->isCommandInstalled('mysqldump', $destination, $current);
    }

    public function isSshInstalled(string $destination = '', string $current = ''): bool
    {
        return $this->isCommandInstalled('ssh', $destination, $current);
    }

    public function isCommandInstalled(string $command, string $destination = '', string $current = ''): bool
    {
        $output = $this->runCommand(ShellBuilder::command($command), $destination, $current);
        if ($output->getExitCode() === 127) {
            return false;
        }
        return stripos(trim($output->getErrorOutput()), 'not found') === false;
    }

    public function getRsyncVersion(string $destination = '', string $current = ''): string
    {
        $command = $this->runCommand(ShellBuilder::command('rsync')->addOption('version'), $destination, $current);
        if (empty($command->getOutput()) && $this->isRsyncInstalled()) {
            throw new CommandExecutionException('Result of rsync --version was empty');
        }
        preg_match('/rsync *version ([0-9.]*).*/', $command->getOutput(), $result);
        return trim($result[1]);
    }

    public function getSshVersion(string $destination = '', string $current = ''): string
    {
        $command = $this->runCommand(ShellBuilder::command('ssh')->addShortOption('V'), $destination, $current);
        if (empty($command->getOutput()) && $command->getExitCode() !== 0) {
            throw new CommandExecutionException('Result of ssh -V was empty');
        }
        // ssh -V writes the version into STDERR instead of STDOUT
        $output = empty($command->getOutput()) ? $command->getErrorOutput() : $command->getOutput();
        preg_match('/OpenSSH_([a-zA-Z0-9\.]*)/', $output, $result);
        return trim($result[1]);
    }

    /**
     * @return array<string, string>
     */
    public function getMysqlDumpVersion(string $destination = '', string $current = ''): array
    {
        $command = ShellBuilder::command('mysqldump')->addShortOption('V');
        $output = $this->runCommand($command, $destination, $current)->getOutput();
        if (empty($output) && $this->isMysqlDumpInstalled()) {
            throw new CommandExecutionException(sprintf('Result of %s was empty', (string)$command));
        }
        preg_match_all(
            '/(.*Ver (?\'dump\'[\d.a-z]+).*)(.*Distrib (?\'mysql\'[\d.a-z]+).*)/m',
            trim($output),
            $matches,
            PREG_SET_ORDER,
            0
        );
        return [
            'mysqlVersion' => $matches[0]['mysql'],
            'dumpVersion' => $matches[0]['dump'],
        ];
    }

    private function runCommand(ShellInterface $command, string $destination = '', string $current = ''): Process
    {
        $output = $this->commandGenerator->sshCommand($destination, $current, $command);
        return $this->commandExecutor->runCommand($output);
    }
}
