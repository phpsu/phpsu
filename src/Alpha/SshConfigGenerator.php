<?php
declare(strict_types=1);

namespace PHPSu\Alpha;

final class SshConfigGenerator
{
    /**
     * @param string $from
     * @param string $to
     * @param SshConnections $sshConnections
     * @return SshConnection[]
     */
    public function findShortestPath(string $from, string $to, SshConnections $sshConnections): array
    {
        $shortestLength = 999999;
        $shortest = '';
        $connections = $this->findAllPaths($from, $to, $sshConnections);
        if (count($connections) > 1) {
            //TODO warning found multiple Connection Possibilities, Selected one of the Shortest. these are all of them: ...
        }
        foreach ($connections as $connectionPath) {
            $length = count($connectionPath);
            if ($length < $shortestLength) {
                $shortest = $connectionPath;
            }
        }
        return $shortest;
    }

    /**
     * @param string $from
     * @param string $to
     * @param SshConnections $sshConnections
     * @return SshConnection[]
     */
    public function findAllPaths(string $from, string $to, SshConnections $sshConnections): array
    {
        if ($from === $to) {
            return [[]];
        }
        $result = [];
        foreach ($sshConnections->getPossibilities($to) as $host => $possibleConnection) {
            if ($host === '') {
                return [[(clone $possibleConnection)->setFrom([])]];
            }
            if ($host === $from) {
                return [[(clone $possibleConnection)->setFrom([$host])]];
            }
            $possibleSubConnections = $this->findAllPaths($from, $host, $sshConnections);
            foreach ($possibleSubConnections as $possibleSubConnection) {
                $possibleSubConnection[] = (clone $possibleConnection)->setFrom([$host]);
                $result[] = $possibleSubConnection;
            }
        }
        return $result;
    }

    public function generate(SshConnections $sshConnections, string $currentHost): SshConfig
    {
        $sshConfig = new SshConfig();
        foreach ($sshConnections->getAllHosts() as $host) {
            $connectionsUsedForPath = $this->findShortestPath($currentHost, $host, $sshConnections);
            foreach ($connectionsUsedForPath as $sshConnection) {
                $fromHosts = $sshConnection->getFrom();
                if (count($fromHosts) > 1) {
                    throw new \Exception('sshConnection Should only have one From at this point!');
                }
                $host = new SshConfigHost();
                $dsn = new DSN($sshConnection->getUrl(), 'ssh');
                if (!$dsn->getUser()) {
                    throw new \Exception(sprintf('user must be specified for ssh connection: %s %s', $sshConnection->getHost(), $sshConnection->getUrl()));
                }
                if (!$dsn->getHost()) {
                    throw new \Exception(sprintf('host must be specified for ssh connection: %s %s', $sshConnection->getHost(), $sshConnection->getUrl()));
                }
                $host->User = $dsn->getUser();
                $host->HostName = $dsn->getHost();
                if ($dsn->getPort() !== 22) {
                    $host->Port = $dsn->getPort();
                }
                if (isset($fromHosts[0]) && $fromHosts[0] !== $currentHost) {
                    $host->ProxyJump = $fromHosts[0];
                }
                $sshConfig->{$sshConnection->getHost()} = $host;
            }
        }
        return $sshConfig;
    }
}
