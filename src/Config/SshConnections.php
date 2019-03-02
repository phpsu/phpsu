<?php
declare(strict_types=1);

namespace PHPSu\Config;

final class SshConnections
{
    private $connections = [];

    /**
     * @return void
     */
    public function addConnections(SshConnection ...$sshConnections)
    {
        foreach ($sshConnections as $sshConnection) {
            $this->add($sshConnection);
        }
    }

    /**
     * @return void
     */
    public function add(SshConnection $sshConnection)
    {
        if (count($sshConnection->getFrom()) === 0) {
            $this->addSingleConnection('', $sshConnection);
        } else {
            foreach ($sshConnection->getFrom() as $from) {
                $this->addSingleConnection($from, $sshConnection);
            }
        }
    }

    /**
     * @return void
     */
    private function addSingleConnection(string $from, SshConnection $sshConnection)
    {
        if (isset($this->connections[$sshConnection->getHost()][$from])) {
            throw new \Exception(sprintf('suspicious Connection Model found: %s->%s has more than one definition', $from, $sshConnection->getHost()));
        }
        $this->connections[$sshConnection->getHost()][$from] = $sshConnection;
    }

    /**
     * @return string[]
     */
    public function getAllHosts(): array
    {
        return array_keys($this->connections);
    }

    /**
     * @param string $to
     * @return SshConnection[]
     */
    public function getPossibilities(string $to): array
    {
        if (!isset($this->connections[$to])) {
            throw new \Exception(sprintf('Host %s not found in SshConnections', $to));
        }
        return $this->connections[$to];
    }
}
