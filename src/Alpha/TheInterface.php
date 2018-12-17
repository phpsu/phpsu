<?php
declare(strict_types=1);

namespace PHPSu\Alpha;

class TheInterface
{
    /**
     * @param \stdClass $globalConfig
     * @param string $from
     * @param string $to
     * @param string $currentHost
     * @return string[]
     */
    public function getCommands(\stdClass $globalConfig, string $from, string $to, string $currentHost): array
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
