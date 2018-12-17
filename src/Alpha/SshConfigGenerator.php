<?php
declare(strict_types=1);

namespace PHPSu\Alpha;

final class SshConfigGenerator
{
    /**
     * @param SshConnection[] $sshConnections
     * @param string $currentHost
     * @return SshConfig
     */
    public function generate(array $sshConnections, string $currentHost): SshConfig
    {
        $sshConfig = new SshConfig();
        foreach ($sshConnections as $sshConnection) {
            $fromHosts = $sshConnection->getFrom();
            if (count($fromHosts) > 0) {
                if (in_array($currentHost, $fromHosts, true)) {
                    $fromHosts = [];
                }
            }
            $host = new SshConfigHost();
            $dsn = new DSN($sshConnection->getUrl(), 'ssh');
            if (!$dsn->getUser()) {
                throw new \Exception(sprintf('user must be specified for ssh connection: %s %s', $sshConnection->getHost(), $sshConnection->getUrl()));
            }
            $host->User = $dsn->getUser();
            $host->HostName = $dsn->getHost();
            if ($dsn->getPort() !== 22) {
                $host->Port = $dsn->getPort();
            }
            if (isset($fromHosts[0])) {
                $host->ProxyJump = $fromHosts[0]; //TODO search for right(shortest) connection between currentHost and this one
            }
            $sshConfig->{$sshConnection->getHost()} = $host;
        }
        return $sshConfig;
    }
}
