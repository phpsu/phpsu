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
        $sshConfig = SshConfig::fromGlobal($globalConfig, $currentHost);
        $result = [];
        $rsyncCommands = RsyncCommand::fromGlobal($globalConfig, $from, $to, $currentHost);
        $sshConfig->setFile(new TempSshConfigFile());
        foreach ($rsyncCommands as $rsyncCommand) {
            $rsyncCommand->setSshConfig($sshConfig);
            $result[] = $rsyncCommand->generate();
        }
        $sshConfig->writeConfig();
        return $result;
    }
}
