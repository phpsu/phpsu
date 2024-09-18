<?php

declare(strict_types=1);

namespace PHPSu\Config;

use Exception;

use function array_merge;

/**
 * @api
 */
final class Database implements DockerTraitSupportInterface
{
    use AddDockerTrait;

    private string $name;

    private DatabaseConnectionDetails $connectionDetails;

    /** @var string[] */
    private array $excludes = [];

    private bool $noDefiner = true;

    private bool $removeNoAutoCreateUser = true;

    /**
     * @var bool https://mariadb.org/mariadb-dump-file-compatibility-change/
     */
    private bool $allowSandboxMode = false;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): Database
    {
        $this->name = $name;
        return $this;
    }

    public function setUrl(string $url): Database
    {
        $this->connectionDetails = DatabaseConnectionDetails::fromUrlString($url);
        return $this;
    }

    public function getConnectionDetails(): DatabaseConnectionDetails
    {
        if ($this->isDockerEnabled() && $this->getContainer() === '') {
            $this->setContainer($this->connectionDetails->getHost());
            $this->connectionDetails->setHost('127.0.0.1');
        }

        if ($this->isDockerEnabled() && $this->connectionDetails->getPort() !== 3306) {
            $this->connectionDetails->setPort(3306);
        }

        return $this->connectionDetails;
    }

    public function setConnectionDetails(DatabaseConnectionDetails $connectionDetails): Database
    {
        $this->connectionDetails = $connectionDetails;
        return $this;
    }

    /**
     * @return string[]
     */
    public function getExcludes(): array
    {
        return $this->excludes;
    }

    /**
     * @param string[] $excludes
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

    public function shouldDefinerBeRemoved(): bool
    {
        return $this->noDefiner;
    }

    public function setRemoveDefinerFromDump(bool $removeIt): Database
    {
        $this->noDefiner = $removeIt;
        return $this;
    }

    public function shouldNoAutoCreateUserRemoved(): bool
    {
        return $this->removeNoAutoCreateUser;
    }

    public function setRemoveNoAutoCreateUser(bool $removeIt): Database
    {
        $this->removeNoAutoCreateUser = $removeIt;
        return $this;
    }

    public function shouldAllowSandboxMode(): bool
    {
        return $this->allowSandboxMode;
    }

    public function setAllowSandboxMode(bool $allowSandboxMode): Database
    {
        $this->allowSandboxMode = $allowSandboxMode;
        return $this;
    }
}
