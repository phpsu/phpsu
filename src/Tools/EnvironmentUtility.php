<?php
declare(strict_types=1);

namespace PHPSu\Tools;

use PHPSu\Controller;
use PHPSu\Exceptions\CommandExecutionException;
use PHPSu\Process\CommandExecutor;

final class EnvironmentUtility
{
    /** @var CommandExecutor  */
    private $commandExecutor;

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
        $output = $this->commandExecutor->executeDirectly($command);
        if ($output->getExitCode() === 127) {
            return false;
        }
        return stripos(trim($output->getErrorOutput()), 'not found') === false;
    }

    public function getRsyncVersion(): string
    {
        $command = $this->commandExecutor->executeDirectly('rsync --version');
        if (empty($command->getOutput())) {
            throw new CommandExecutionException('Result of rsync --version was empty');
        }
        preg_match('/rsync *version ([0-9.]*).*/', $command->getOutput(), $result);
        return trim($result[1]);
    }

    public function getSshVersion(): string
    {
        $command = $this->commandExecutor->executeDirectly('ssh -V');
        if (empty($command->getOutput()) && $command->getExitCode() !== 0) {
            throw new CommandExecutionException('Result of ssh -V was empty');
        }
        // ssh -V writes the version into STDERR instead of STDOUT
        $output = empty($command->getOutput()) ? $command->getErrorOutput() : $command->getOutput();
        preg_match('/OpenSSH_([a-zA-Z0-9\.]*)/', $output, $result);
        return trim($result[1]);
    }

    public function getMysqlDumpVersion(): array
    {
        $output = $this->commandExecutor->executeDirectly('mysqldump -V')->getOutput();
        if (empty($output)) {
            throw new CommandExecutionException('Result of mysqldump -V was empty');
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

    public function getInstalledPackageVersion(string $packageName): ?string
    {
        $activeInstallations = json_decode(file_get_contents($this->spotVendorPath() . '/composer/installed.json'));
        foreach ($activeInstallations as $installed) {
            if ($installed->name === $packageName) {
                return $installed->version;
            }
        }
        return null;
    }

    private function spotVendorPath(): string
    {
        if (file_exists(Controller::PHPSU_ROOT_PATH . '/../autoload.php')) {
            return Controller::PHPSU_ROOT_PATH . '/../';
        }
        return Controller::PHPSU_ROOT_PATH . '/vendor/';
    }

    public function getSymfonyProcessVersion(): string
    {
        return str_replace('v', '', $this->getInstalledPackageVersion('symfony/process'));
    }

    public function getSymfonyConsoleVersion(): string
    {
        return str_replace('v', '', $this->getInstalledPackageVersion('symfony/console'));
    }
}
