<?php
declare(strict_types=1);

namespace PHPSu\Alpha;

final class Databases
{
    /** @var Database[] */
    private $databases = [];

    public function add(Database $database)
    {
        $this->databases[$database->getName()] = $database;
    }

    /**
     * @return Database[]
     */
    public function getAll(): array
    {
        return $this->databases;
    }
}
