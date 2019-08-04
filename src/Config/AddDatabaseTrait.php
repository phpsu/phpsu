<?php
declare(strict_types=1);

namespace PHPSu\Config;

trait AddDatabaseTrait
{
    /** @var Databases */
    private $databases;

    /** @var DatabaseConnections */
    private $databaseConnections;

    public function addDatabaseObject(Database $database): self
    {
        $connection = DatabaseConnection::fromDatabaseObject($database);
        $this->databaseConnections->add($connection);
        return $this;
    }

    public function addDatabase(string $name, string $url): Database
    {
        $database = new Database();
        $database->setName($name)->setUrl($url);
        $this->addDatabaseObject($database);
        return $database;
    }
}
