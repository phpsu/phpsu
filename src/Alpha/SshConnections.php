<?php
declare(strict_types=1);

namespace PHPSu\Alpha;

final class SshConnections
{
    private $connections = [];

    public function addConnections(SshConnection ...$sshConnections): void
    {
        foreach ($sshConnections as $sshConnection) {
            $this->addConnection($sshConnection);
        }
    }

    public function addConnection(SshConnection $sshConnection): void
    {
        if (count($sshConnection->getFrom()) === 0) {
            $this->addSingleConnection('', $sshConnection);
        } else {
            foreach ($sshConnection->getFrom() as $from) {
                $this->addSingleConnection($from, $sshConnection);
            }
        }
    }

    private function addSingleConnection(string $from, SshConnection $sshConnection): void
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
        return $this->connections[$to];
    }
}
