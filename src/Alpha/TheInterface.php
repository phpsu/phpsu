<?php
declare(strict_types=1);

namespace PHPSu\Alpha;

class TheInterface
{
    /**
     * TODO:
     **can:
     * filesystems from global config
     *
     **can't:
     * filesystems from app config
     *
     * database from global config
     * database from app config
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
        $sshConfig = SshConfig::fromGlobal($globalConfig, $currentHost);
        $sshConfig->setFile(new TempSshConfigFile());

        $result = [];
        $rsyncCommands = RsyncCommand::fromGlobal($globalConfig, $from, $to, $currentHost);
        foreach ($rsyncCommands as $rsyncCommand) {
            $rsyncCommand->setSshConfig($sshConfig);
            $result[] = $rsyncCommand->generate();
        }
        $databaseCommands = DatabaseCommand::fromGlobal($globalConfig, $from, $to, $currentHost);
        foreach ($databaseCommands as $databaseCommand) {
            $databaseCommand->setSshConfig($sshConfig);
            $result[] = $databaseCommand->generate();
        }
        $sshConfig->writeConfig();
        return $result;
    }
}
