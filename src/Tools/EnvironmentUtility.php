<?php

declare(strict_types=1);

namespace PHPSu\Tools;

use PHPSu\Command\SshCommand;
use PHPSu\Config\AppInstance;
use PHPSu\Config\GlobalConfig;
use PHPSu\Config\SshConfig;
use PHPSu\Config\TempSshConfigFile;
use PHPSu\Controller;
use PHPSu\Exceptions\CommandExecutionException;
use PHPSu\Process\CommandExecutor;
use PHPSu\ShellCommandBuilder\Exception\ShellBuilderException;
use PHPSu\ShellCommandBuilder\ShellBuilder;
use PHPSu\ShellCommandBuilder\ShellInterface;

/**
 * @internal
 */
final class EnvironmentUtility
{
    /** @var CommandExecutor */
    private $commandExecutor;
    /** @var string */
    private $phpsuRootPath;
    /** @var GlobalConfig */
    private $globalConfig;

    public function __construct(CommandExecutor $executor = null)
    {
        $this->commandExecutor = $executor ?? new CommandExecutor();
        $this->phpsuRootPath = Controller::PHPSU_ROOT_PATH;
    }

    public function setGlobalConfig(GlobalConfig $config): void
    {
        $this->globalConfig = $config;
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

    /**
     * @param string $command
     * @return bool
     * @throws ShellBuilderException
     */
    public function isCommandInstalled(string $command): bool
    {
        $output = $this->commandExecutor->runCommand(ShellBuilder::command($command));
        if ($output->getExitCode() === 127) {
            return false;
        }
        return stripos(trim($output->getErrorOutput()), 'not found') === false;
    }

    /**
     * @param AppInstance $instance
     * @param ShellInterface $command
     * @return bool
     * @throws ShellBuilderException
     */
    public function isInstalledOnInstance(AppInstance $instance, ShellInterface $command): bool
    {
        $shell = new SshCommand();
        $shell->setCommand(ShellBuilder::command('sh')->addShortOption('c', $command));
        $shell->setInto($instance->getHost());
        $sshConfig = SshConfig::fromGlobal($this->globalConfig, $instance->getHost());
        $shell->setSshConfig($sshConfig);
        $result = $shell->generate(ShellBuilder::new());
        $output = $this->commandExecutor->runCommand($result);
        var_dump($output->getExitCodeText());
        if ($output->getExitCode() === 127 || $output->getExitCode() === 255) {
            return false;
        }
        return stripos(trim($output->getErrorOutput()), 'not found') === false;
    }

    public function getRsyncVersion(): string
    {
        $command = $this->commandExecutor->runCommand(ShellBuilder::command('rsync')->addOption('version'));
        if (empty($command->getOutput()) && $this->isRsyncInstalled()) {
            throw new CommandExecutionException('Result of rsync --version was empty');
        }
        preg_match('/rsync *version ([0-9.]*).*/', $command->getOutput(), $result);
        return trim($result[1]);
    }

    public function getSshVersion(): string
    {
        $command = $this->commandExecutor->runCommand(ShellBuilder::command('ssh')->addShortOption('V'));
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
     * @throws CommandExecutionException
     * @throws ShellBuilderException
     */
    public function getMysqlDumpVersion(): array
    {
        $command = ShellBuilder::command('mysqldump')->addShortOption('V');
        $output = $this->commandExecutor->runCommand($command)->getOutput();
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

    /**
     * @param string $packageName
     * @return string|null
     */
    public function getInstalledPackageVersion(string $packageName): ?string
    {
        $contents = file_get_contents($this->spotVendorPath() . '/composer/installed.json') ?: '';
        $activeInstallations = json_decode($contents, false);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return null;
        }
        // support both composer 1.0 and composer 2.0
        $installations = $activeInstallations->packages ?? $activeInstallations;
        foreach ($installations as $installed) {
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
