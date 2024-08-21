<?php

declare(strict_types=1);

namespace PHPSu\Tools;

use PHPSu\Controller;
use PHPSu\Exceptions\CommandExecutionException;
use PHPSu\Process\CommandExecutor;
use PHPSu\ShellCommandBuilder\ShellBuilder;
use stdClass;
use Symfony\Component\Process\Exception\ProcessStartFailedException;

/**
 * @internal
 */
final class EnvironmentUtility
{
    private readonly CommandExecutor $commandExecutor;

    private string $phpsuRootPath = Controller::PHPSU_ROOT_PATH;

    public function __construct(CommandExecutor $executor = null)
    {
        $this->commandExecutor = $executor ?? new CommandExecutor();
    }

    public function isRsyncInstalled(): bool
    {
        return $this->isCommandInstalled('rsync');
    }

    public function isMysqlDumpInstalled(): bool
    {
        return $this->isCommandInstalled('mysqldump');
    }

    public function isSshInstalled(): bool
    {
        return $this->isCommandInstalled('ssh');
    }

    public function isCommandInstalled(string $command): bool
    {
        try {
            $output = $this->commandExecutor->runCommand(ShellBuilder::command($command));
        } catch (ProcessStartFailedException) {
            return false;
        }

        if ($output->getExitCode() === 127) {
            return false;
        }

        return stripos(trim($output->getErrorOutput()), 'not found') === false;
    }

    public function getRsyncVersion(): string
    {
        $command = $this->commandExecutor->runCommand(ShellBuilder::command('rsync')->addOption('version'));
        if (!$command->getOutput() && $this->isRsyncInstalled()) {
            throw new CommandExecutionException('Result of rsync --version was empty');
        }

        preg_match('/rsync *version ([0-9.]*).*/', $command->getOutput(), $result);
        return trim($result[1]);
    }

    public function getSshVersion(): string
    {
        $command = $this->commandExecutor->runCommand(ShellBuilder::command('ssh')->addShortOption('V'));
        if (!$command->getOutput() && $command->getExitCode() !== 0) {
            throw new CommandExecutionException('Result of ssh -V was empty');
        }

        // ssh -V writes the version into STDERR instead of STDOUT
        $output = $command->getOutput() ?: $command->getErrorOutput();
        preg_match('/OpenSSH_([a-zA-Z0-9\.]*)/', $output, $result);
        return trim($result[1]);
    }

    /**
     * @return array<string, string>
     */
    public function getMysqlDumpVersion(): array
    {
        $command = ShellBuilder::command('mysqldump')->addShortOption('V');
        $output = $this->commandExecutor->runCommand($command)->getOutput();
        if (!$output && $this->isMysqlDumpInstalled()) {
            throw new CommandExecutionException(sprintf('Result of %s was empty', (string)$command));
        }

        preg_match_all(
            '/(.*Ver (?\'dump\'[\d.]+).*)/m',
            trim($output),
            $matchesDump,
            PREG_SET_ORDER,
            0
        );
        preg_match_all(
            '/(.*Distrib (?\'mysql\'[\d.]+).*){0,1}/m',
            trim($output),
            $matchesMysql,
            PREG_SET_ORDER,
            0
        );
        return [
            'mysqlVersion' => $matchesMysql[0]['mysql'] ?? $matchesDump[0]['dump'] ?? '-',
            'dumpVersion' => $matchesDump[0]['dump'] ?? '-',
        ];
    }

    public function getInstalledPackageVersion(string $packageName): ?string
    {
        $contents = file_get_contents($this->spotVendorPath() . '/composer/installed.json') ?: '';
        $activeInstallations = json_decode($contents, false);
        if (!($activeInstallations instanceof stdClass) || json_last_error() !== JSON_ERROR_NONE) {
            return null;
        }

        foreach ($activeInstallations->packages as $installed) {
            if ($installed->name === $packageName) {
                return $installed->version;
            }
        }

        return null;
    }

    private function spotVendorPath(): string
    {
        if (file_exists($this->phpsuRootPath . '/../../autoload.php')) {
            // installed via composer require
            return $this->phpsuRootPath . '/../../';
        }

        // in dev installation
        return $this->phpsuRootPath . '/vendor/';
    }
}
