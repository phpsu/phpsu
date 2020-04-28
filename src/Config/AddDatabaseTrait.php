<?php

declare(strict_types=1);

namespace PHPSu\Config;

trait AddDatabaseTrait
{
    /** @var Databases */
    private $databases;

    public function addDatabaseObject(Database $database): self
    {
        $this->databases->add($database);
        return $this;
    }

    public function addDatabase(string $name, string $database, string $user = '', string $password = '', string $host = '127.0.0.1', int $port = 3306): Database
    {
        if (strpos($database, '@') > 0) {
            $url = $database;
            $warn = self::class . '->addDatabase with an Url as second parameter has been renamed to addDatabaseByUrl. method addDatabase will lose this functionality in a future release.' . PHP_EOL;
            trigger_error($warn, E_USER_DEPRECATED);
            return $this->addDatabaseByUrl($name, $url);
        }
        $databaseObject = new Database();
        $connectionDetails = DatabaseConnectionDetails::fromDetails($database, $user, $password, $host, $port);
        $databaseObject->setName($name)->setConnectionDetails($connectionDetails);
        $this->databases->add($databaseObject);
        return $databaseObject;
    }

    public function addDatabaseByUrl(string $name, string $url): Database
    {
        $database = new Database();
        $database->setName($name)->setUrl($url);
        $this->databases->add($database);
        return $database;
    }
}
