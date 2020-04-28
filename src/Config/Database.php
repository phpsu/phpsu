<?php

declare(strict_types=1);

namespace PHPSu\Config;

use function array_merge;

/**
 * @api
 */
final class Database
{
    /** @var string */
    private $name;

    /** @var DatabaseConnectionDetails */
    private $connectionDetails;

    /** @var string[] */
    private $excludes = [];

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): Database
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @deprecated will be removed in 3.0
     */
    public function getUrl(): string
    {
        return $this->connectionDetails->__toString();
    }

    /**
     * @param string $url
     * @return $this
     */
    public function setUrl(string $url): Database
    {
        $this->connectionDetails = DatabaseConnectionDetails::fromUrlString($url);
        return $this;
    }

    public function getConnectionDetails(): DatabaseConnectionDetails
    {
        return $this->connectionDetails;
    }

    public function setConnectionDetails(DatabaseConnectionDetails $connectionDetails): Database
    {
        $this->connectionDetails = $connectionDetails;
        return $this;
    }

    /**
     * @return array<string>
     */
    public function getExcludes(): array
    {
        return $this->excludes;
    }

    /**
     * @param array<string> $excludes
     * @return Database
     * @return Database
     */
    public function addExcludes(array $excludes): Database
    {
        $this->excludes = array_merge($this->excludes, $excludes);
        return $this;
    }

    public function addExclude(string $exclude): Database
    {
        $this->excludes[] = $exclude;
        return $this;
    }
}
