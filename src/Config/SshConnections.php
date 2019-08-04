<?php
declare(strict_types=1);

namespace PHPSu\Config;

use Exception;
use PHPSu\Exceptions\ConfigurationException;
use function count;

final class SshConnections implements ConnectionsInterface
{
    /** @var SshConnection[] */
    private $connections = [];

    /** @var ?array<string, array<string, SshConnection>> */
    private $compiled;

    /**
     * @param ConnectionInterface[] $sshConnections
     * @return void
     */
    public function addConnections(ConnectionInterface ...$sshConnections)
    {
        foreach ($sshConnections as $sshConnection) {
            $this->add($sshConnection);
        }
    }

    /**
     * @param ConnectionInterface $sshConnection
     * @return void
     */
    public function add(ConnectionInterface $sshConnection)
    {
        $this->connections[] = $sshConnection;
        $this->compiled = null;
    }

    /**
     * @return void
     */
    public function compile()
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
            throw new ConfigurationException(sprintf('the source and destination Host can not be the same: %s', $source));
        }
        if (isset($this->compiled[$destination][$source])) {
            throw new ConfigurationException(sprintf('suspicious Connection Model found: %s->%s has more than one definition', $source, $destination));
        }
        $compiled[$destination][$source] = $sshConnection;
        return $compiled;
    }

    /**
     * @return string[]
     */
    public function getAllHosts(): array
    {
        return array_keys($this->getCompiled());
    }

    /**
     * @param string $to
     * @return array<string, SshConnection>
     */
    public function getPossibilities(string $to): array
    {
        $compiled = $this->getCompiled();
        if (!isset($compiled[$to])) {
            throw new ConfigurationException(sprintf('Host %s not found in SshConnections', $to));
        }
        return $compiled[$to];
    }
}
