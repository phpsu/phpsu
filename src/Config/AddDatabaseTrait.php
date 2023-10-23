<?php

declare(strict_types=1);

namespace PHPSu\Config;

/**
 * @internal
 */
trait AddDatabaseTrait
{
    private Databases $databases;

    /**
     * @api
     */
    public function addDatabaseObject(Database $database): self
    {
        $this->databases->add($database);
        return $this;
    }

    /**
     * @api
     */
    public function addDatabase(string $name, string $database, string $user, string $password, string $host = '127.0.0.1', int $port = 3306, string $databaseType = 'mysql'): Database
    {
        $databaseObject = new Database();
        $connectionDetails = DatabaseConnectionDetails::fromDetails($database, $user, $password, $host, $port, $databaseType);
        $databaseObject->setName($name)->setConnectionDetails($connectionDetails);
        $this->databases->add($databaseObject);
        return $databaseObject;
    }

    /**
     * @api
     */
    public function addDatabaseByUrl(string $name, string $url): Database
    {
        $database = new Database();
        $database->setName($name)->setUrl($url);
        $this->databases->add($database);
        return $database;
    }
}
