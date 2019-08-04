<?php

namespace PHPSu\Config;

interface DatabaseConfigurationInterface
{
    public function getHost(): string;

    public function setHost(string $host): SqlDatabaseConfiguration;

    public function getDatabase(): string;

    public function setDatabase(string $database): SqlDatabaseConfiguration;

    public function __toString(): string;
}
