<?php
declare(strict_types=1);

namespace PHPSu\Tools;

use PHPSu\Process\CommandExecutor;
use PHPSu\Process\Process;

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

    public function isCommandInstalled(string $command): bool
    {
        $output = $this->commandExecutor->executeDirectly($command);
        if ($output[2] === 127) {
            return false;
        }
        return stripos(trim($output[1]), 'not found') === false;
    }

    public function getRsyncVersion(): string
    {
        $command = $this->commandExecutor->executeDirectly('rsync --version');
        preg_match('rsync *version ([0-9.]*).*$', $command[0], $result);
        return trim($result[1]);
    }

    public function getMysqlDumpVersion(): array
    {
        $output = $this->commandExecutor->executeDirectly('mysqldump -V');
        preg_match_all(
            '/(.*Ver (?\'dump\'[\d.a-z]+).*)(.*Distrib (?\'mysql\'[\d.a-z]+).*)/m',
            trim($output[0]),
            $matches,
            PREG_SET_ORDER,
            0
        );
        return [
            'mysqlVersion' => $matches[0]['mysql'],
            'dumpVersion' => $matches[0]['dump'],
        ];
    }

    public function isGitInstalled(): bool
    {
        return $this->isCommandInstalled('git');
    }

    public function getInstalledPackageVersion(string $packageName): string
    {
        $packageVersion = '';
        $activeInstallations = json_decode(file_get_contents(PHPSU_VENDOR_PATH . '/composer/installed.json'));
        foreach ($activeInstallations as $installed) {
            if ($installed->name === $packageName) {
                $packageVersion = $installed->version;
            }
        }
        return $packageVersion;
    }

    public function getSymfonyProcessVersion(): string
    {
        return str_replace('v', '', $this->getInstalledPackageVersion('symfony/process'));
    }

    public function getSymfonyConsoleVersion(): string
    {
        return str_replace('v', '', $this->getInstalledPackageVersion('symfony/console'));
    }

    public function isWindows(): bool
    {
        return stripos(PHP_OS, 'WIN') === 0;
    }
}
