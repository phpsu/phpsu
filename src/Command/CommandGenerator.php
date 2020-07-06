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
    /** @var SplFileObject|null */
    private $file;
    /** @var GlobalConfig */
    private $globalConfig;
    /** @var int */
    private $verbosity;

    public function __construct(GlobalConfig $globalConfig, int $verbosity = OutputInterface::VERBOSITY_NORMAL)
    {
        $this->globalConfig = $globalConfig;
        $this->verbosity = $verbosity;
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

    public function sshCommand(string $destination, string $currentHost, ?ShellInterface $command): string
    {
        $sshConfig = SshConfig::fromGlobal($this->globalConfig, $currentHost);
        $sshConfig->setFile($this->getFile());
        $sshCommand = SshCommand::fromGlobal($this->globalConfig, $destination, $currentHost, $this->verbosity);
        $sshCommand->setSshConfig($sshConfig);
        $sshConfig->writeConfig();
        return (string)$sshCommand->generate(ShellBuilder::new(), $command);
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
