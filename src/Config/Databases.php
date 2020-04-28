<?php

declare(strict_types=1);

namespace PHPSu\Config;

use Exception;

/**
 * @internal
 */
final class Databases
{
    /** @var Database[] */
    private $databases = [];

    public function add(Database $database): Databases
    {
        $this->databases[$database->getName()] = $database;
        return $this;
    }

    /**
     * @return Database[]
     */
    public function getAll(): array
    {
        return $this->databases;
    }

    public function has(string $name): bool
    {
        return isset($this->databases[$name]);
    }

    public function get(string $name): Database
    {
        if (!isset($this->databases[$name])) {
            throw new Exception(sprintf('Database %s not found', $name));
        }
        return $this->databases[$name];
    }
}
