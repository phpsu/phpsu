<?php

declare(strict_types=1);

namespace PHPSu\Config;

use Exception;

/**
 * @api
 */
final class SshConnections
{
    /** @var SshConnection[] */
    private $connections = [];

    /** @var ?array<string, array<string, SshConnection>> */
    private $compiled;


    public function addConnections(SshConnection ...$sshConnections): void
    {
        foreach ($sshConnections as $sshConnection) {
            $this->add($sshConnection);
        }
    }


    public function add(SshConnection $sshConnection): void
    {
        $this->connections[] = $sshConnection;
        $this->compiled = null;
    }


    public function compile(): void
    {
        $this->getCompiled();
    }

    /**
     * @return array<string, array<string, SshConnection>>
     */
    private function getCompiled(): array
    {
        if ($this->compiled === null) {
            $this->compiled = [];
            foreach ($this->connections as $connection) {
                if (count($connection->getFrom()) === 0) {
                    $this->compiled = $this->addCompiledSingleConnection('', $connection, $this->compiled);
                } else {
                    foreach ($connection->getFrom() as $from) {
                        $this->compiled = $this->addCompiledSingleConnection($from, $connection, $this->compiled);
                    }
                }
            }
        }
        return $this->compiled;
    }

    /**
     * @param string $source
     * @param SshConnection $sshConnection
     * @param array<string, array<string, SshConnection>> $compiled
     * @return array<string, array<string, SshConnection>>
     * @throws Exception
     */
    private function addCompiledSingleConnection(string $source, SshConnection $sshConnection, array $compiled): array
    {
        $destination = $sshConnection->getHost();
        if ($source === $destination) {
            throw new Exception(sprintf('the source and destination Host can not be the same: %s', $source));
        }
        if ($this->compiled && isset($this->compiled[$destination][$source])) {
            throw new Exception(sprintf('suspicious Connection Model found: %s->%s has more than one definition', $source, $destination));
        }
        $compiled[$destination][$source] = $sshConnection;
        return $compiled;
    }

    /**
     * @return array<int, int|string>
     */
    public function getAllHosts(): array
    {
        $compiled = $this->getCompiled();
        return array_keys($compiled);
    }

    /**
     * @param string $to
     * @return array<string, SshConnection>
     */
    public function getPossibilities(string $to): array
    {
        $compiled = $this->getCompiled();
        if (!isset($compiled[$to])) {
            throw new Exception(sprintf('Host %s not found in SshConnections', $to));
        }
        return $compiled[$to];
    }
}
