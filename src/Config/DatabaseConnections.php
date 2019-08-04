<?php
declare(strict_types=1);

namespace PHPSu\Config;

use InvalidArgumentException;

final class DatabaseConnections implements ConnectionsInterface
{
    /** @var DatabaseConnection[] */
    private $connections = [];

    /**
     * @param ConnectionInterface[] $databaseConnections
     * @return void
     */
    public function addConnections(ConnectionInterface ...$databaseConnections)
    {
        foreach ($databaseConnections as $databaseConnection) {
            $this->add($databaseConnection);
        }
    }

    /**
     * @param DatabaseConnection|ConnectionInterface $databaseConnection
     * @return void
     */
    public function add(ConnectionInterface $databaseConnection)
    {
        $this->connections[$databaseConnection->getIdentifier()] = $databaseConnection;
    }

    public function getAll(): array
    {
        return $this->connections;
    }
    
    public function get(string $identifier)
    {
        if (!isset($this->connections[$identifier])) {
            throw new InvalidArgumentException('This bullshit doesnt exist'); // todo: error message
        }
    }
}
