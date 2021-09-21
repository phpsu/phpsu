<?php

declare(strict_types=1);

namespace PHPSu\Command;

use Exception;
use PHPSu\Config\GlobalConfig;
use PHPSu\Config\SshConfig;
use PHPSu\Config\TempSshConfigFile;
use PHPSu\Options\SyncOptions;
use PHPSu\ShellCommandBuilder\ShellBuilder;
use PHPSu\ShellCommandBuilder\ShellInterface;
use SplFileObject;
use Symfony\Component\Console\Output\OutputInterface;

use function in_array;

/**
 * @internal
 */
final class CommandGenerator
{
    private ?SplFileObject $file = null;
    private GlobalConfig $globalConfig;
    private int $verbosity = OutputInterface::VERBOSITY_NORMAL;

    public function __construct(GlobalConfig $globalConfig)
    {
        $this->globalConfig = $globalConfig;
    }

    public function setVerbosity(int $verbosity): self
    {
        $this->verbosity = $verbosity;
        return $this;
    }

    public function getFile(): SplFileObject
    {
        if (!$this->file instanceof SplFileObject) {
            $this->file = new TempSshConfigFile();
        }
        return $this->file;
    }

    public function setFile(SplFileObject $file = null): CommandGenerator
    {
        $this->file = $file;
        return $this;
    }

    public function sshCommand(string $destination, string $currentHost, ?ShellInterface $command): ShellInterface
    {
        $sshConfig = SshConfig::fromGlobal($this->globalConfig, $currentHost);
        $sshConfig->setFile($this->getFile());
        $sshCommand = SshCommand::fromGlobal($this->globalConfig, $destination, $currentHost, $this->verbosity);
        $sshCommand->setSshConfig($sshConfig);
        $sshConfig->writeConfig();
        $sshCommand->setCommand($command);
        return $sshCommand->generate(ShellBuilder::new());
    }

    public function mysqlCommand(string $instance, ?string $database, ?string $command): ShellInterface
    {
        $sshConfig = SshConfig::fromGlobal($this->globalConfig, $instance);
        $sshConfig->setFile($this->getFile());
        $mysqlCommand = MysqlCommand::fromGlobal($this->globalConfig, $instance, $database, $this->verbosity);
        $mysqlCommand->setCommand($command);
        $mysqlCommand->setSshConfig($sshConfig);
        $sshConfig->writeConfig();
        return $mysqlCommand->generate();
    }

    /**
     * @param SyncOptions $options
     * @return string[]
     * @throws Exception
     */
    public function syncCommands(SyncOptions $options): array
    {
        if ($options->getSource() === $options->getDestination()) {
            throw new Exception(sprintf('Source and Destination are Identical: %s', $options->getSource()));
        }
        if (!in_array($options->getCurrentHost(), ['', 'local'], true)) {
            $this->globalConfig->validateConnectionToHost($options->getCurrentHost());
        }
        $sshConfig = SshConfig::fromGlobal($this->globalConfig, $options->getCurrentHost());
        $sshConfig->setFile($this->getFile());

        $result = [];
        if (!$options->isNoFiles()) {
            $rsyncCommands = RsyncCommand::fromGlobal($this->globalConfig, $options->getSource(), $options->getDestination(), $options->getCurrentHost(), $options->isAll(), $this->verbosity);
            foreach ($rsyncCommands as $rsyncCommand) {
                $rsyncCommand->setSshConfig($sshConfig);
                $result[$rsyncCommand->getName()] = (string)$rsyncCommand->generate(ShellBuilder::new());
            }
        }
        if (!$options->isNoDatabases()) {
            $databaseCommands = DatabaseCommand::fromGlobal($this->globalConfig, $options->getSource(), $options->getDestination(), $options->getCurrentHost(), $options->isAll(), $this->verbosity);
            foreach ($databaseCommands as $databaseCommand) {
                $databaseCommand->setSshConfig($sshConfig);
                $result[$databaseCommand->getName()] = (string)$databaseCommand->generate(ShellBuilder::new());
            }
        }
        $sshConfig->writeConfig();
        return $result;
    }
}
