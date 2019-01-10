<?php
declare(strict_types=1);

namespace PHPSu\Command;

use PHPSu\Config\GlobalConfig;
use PHPSu\Config\SshConfig;
use PHPSu\Config\TempSshConfigFile;

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

    /**
     * @param string $from
     * @param string $to
     * @param string $currentHost
     * @return string[]
     */
    public function syncCommands(string $from, string $to, string $currentHost): array
    {
        if ($from === $to) {
            throw new \Exception(sprintf('From and To are Identical: %s', $from));
        }
        if ($currentHost !== '') {
            $this->globalConfig->validateConnectionToHost($currentHost);
        }
        $sshConfig = SshConfig::fromGlobal($this->globalConfig, $currentHost);
        $sshConfig->setFile($this->getFile());

        $result = [];
        $rsyncCommands = RsyncCommand::fromGlobal($this->globalConfig, $from, $to, $currentHost);
        foreach ($rsyncCommands as $rsyncCommand) {
            $rsyncCommand->setSshConfig($sshConfig);
            $result[$rsyncCommand->getName()] = $rsyncCommand->generate();
        }
        $databaseCommands = DatabaseCommand::fromGlobal($this->globalConfig, $from, $to, $currentHost);
        foreach ($databaseCommands as $databaseCommand) {
            $databaseCommand->setSshConfig($sshConfig);
            $result[$databaseCommand->getName()] = $databaseCommand->generate();
        }
        $sshConfig->writeConfig();
        return $result;
    }

    public function sshCommand(string $destination, string $currentHost): string
    {
        $sshConfig = SshConfig::fromGlobal($this->globalConfig, $currentHost);
        $sshConfig->setFile($this->getFile());
        $sshCommand = SshCommand::fromGlobal($this->globalConfig, $destination, $currentHost);
        $sshCommand->setSshConfig($sshConfig);
        $sshConfig->writeConfig();
        return $sshCommand->generate();
    }
}
