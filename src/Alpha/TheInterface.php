<?php
declare(strict_types=1);

namespace PHPSu\Alpha;

class TheInterface
{
    /** @var \SplFileObject */
    private $file;

    public function __construct()
    {
        $this->file = new TempSshConfigFile();
    }

    public function getFile(): \SplFileObject
    {
        return $this->file;
    }

    public function setFile(\SplFileObject $file): TheInterface
    {
        $this->file = $file;
        return $this;
    }

    /**
     * TODO:
     **can't:
     * directly from server to server:
     * - rsync dose that automatically: https://unix.stackexchange.com/questions/183504/how-to-rsync-files-between-two-remotes/183516#183516
     * - mysql needs this from us
     *
     * @param GlobalConfig $globalConfig
     * @param string $from
     * @param string $to
     * @param string $currentHost
     * @return string[]
     */
    public function getCommands(GlobalConfig $globalConfig, string $from, string $to, string $currentHost): array
    {
        if ($from === $to) {
            throw new \Exception(sprintf('From and To are Identical: %s', $from));
        }
        if ($currentHost !== '') {
            $globalConfig->validateConnectionToHost($currentHost);
        }
        $sshConfig = SshConfig::fromGlobal($globalConfig, $currentHost);
        $sshConfig->setFile($this->file);

        $result = [];
        $rsyncCommands = RsyncCommand::fromGlobal($globalConfig, $from, $to, $currentHost);
        foreach ($rsyncCommands as $rsyncCommand) {
            $rsyncCommand->setSshConfig($sshConfig);
            $result[$rsyncCommand->getName()] = $rsyncCommand->generate();
        }
        $databaseCommands = DatabaseCommand::fromGlobal($globalConfig, $from, $to, $currentHost);
        foreach ($databaseCommands as $databaseCommand) {
            $databaseCommand->setSshConfig($sshConfig);
            $result[$databaseCommand->getName()] = $databaseCommand->generate();
        }
        $sshConfig->writeConfig();
        return $result;
    }
}
