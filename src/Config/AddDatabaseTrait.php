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

    public function addDatabase(string $name, string $url): Database
    {
        $database = new Database();
        $database->setName($name)->setUrl($url);
        $this->databases->add($database);
        return $database;
    }
}
