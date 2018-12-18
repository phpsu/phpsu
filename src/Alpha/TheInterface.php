<?php
declare(strict_types=1);

namespace PHPSu\Alpha;

class TheInterface
{
    /**
     **can:
     * filesystems from global config
     * database from global config
     *
     * TODO:
     **can't:
     * filesystems from app config
     * database from app config
     * directly from server to server: rsync dose that automatically: https://unix.stackexchange.com/questions/183504/how-to-rsync-files-between-two-remotes/183516#183516
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
            //for validation:
            $globalConfig->sshConnections->getPossibilities($currentHost);
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
