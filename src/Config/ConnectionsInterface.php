<?php
declare(strict_types=1);

namespace PHPSu\Config;

interface ConnectionsInterface
{
    /**
     * @param ConnectionInterface[] $sshConnections
     * @return void
     */
    public function addConnections(ConnectionInterface ...$sshConnections);

    /**
     * @param ConnectionInterface $sshConnection
     * @return void
     */
    public function add(ConnectionInterface $sshConnection);
}
