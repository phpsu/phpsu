<?php
declare(strict_types=1);

namespace PHPSu\Command;

use PHPSu\Config\GlobalConfig;
use PHPSu\Config\SshConfig;
use PHPSu\Config\TempSshConfigFile;
use PHPSu\SyncOptions;

final class CommandGenerator
{
    /** @var \SplFileObject */
    private $file;
    /** @var GlobalConfig */
    private $globalConfig;

    public function __construct(GlobalConfig $globalConfig)
    {
        $this->globalConfig = $globalConfig;
    }

    public function getFile(): \SplFileObject
    {
        if (!$this->file instanceof \SplFileObject) {
            $this->file = new TempSshConfigFile();
        }
        return $this->file;
    }

    public function setFile(\SplFileObject $file): CommandGenerator
    {
        $this->file = $file;
        return $this;
    }

    public function sshCommand(string $destination, string $currentHost, string $command): string
    {
        $sshConfig = SshConfig::fromGlobal($this->globalConfig, $currentHost);
        $sshConfig->setFile($this->getFile());
        $sshCommand = SshCommand::fromGlobal($this->globalConfig, $destination, $currentHost);
        $sshCommand->setSshConfig($sshConfig);
        $sshConfig->writeConfig();
        return $sshCommand->generate($command);
    }

    /**
     * @param SyncOptions $options
     * @return string[]
     * @throws \Exception
     */
    public function syncCommands(SyncOptions $options): array
    {
        if ($options->getSource() === $options->getDestination()) {
            throw new \Exception(sprintf('Source and Destination are Identical: %s', $options->getSource()));
        }
        if (!\in_array($options->getCurrentHost(), ['', 'local'], true)) {
            $this->globalConfig->validateConnectionToHost($options->getCurrentHost());
        }
        $sshConfig = SshConfig::fromGlobal($this->globalConfig, $options->getCurrentHost());
        $sshConfig->setFile($this->getFile());

        $result = [];
        if ($options->isNoFiles() === false) {
            $rsyncCommands = RsyncCommand::fromGlobal($this->globalConfig, $options->getSource(), $options->getDestination(), $options->getCurrentHost(), $options->isAll());
            foreach ($rsyncCommands as $rsyncCommand) {
                $rsyncCommand->setSshConfig($sshConfig);
                $result[$rsyncCommand->getName()] = $rsyncCommand->generate();
            }
        }
        if ($options->isNoDatabases() === false) {
            $databaseCommands = DatabaseCommand::fromGlobal($this->globalConfig, $options->getSource(), $options->getDestination(), $options->getCurrentHost(), $options->isAll());
            foreach ($databaseCommands as $databaseCommand) {
                $databaseCommand->setSshConfig($sshConfig);
                $result[$databaseCommand->getName()] = $databaseCommand->generate();
            }
        }
        $sshConfig->writeConfig();
        return $result;
    }
}
