<?php

declare(strict_types=1);

namespace PHPSu\Config;

use InvalidArgumentException;
use PHPSu\Config\Compression\CompressionInterface;

/**
 * @api
 */
final class AppInstance
{
    use AddFilesystemTrait;
    use AddDatabaseTrait;

    private string $name;

    private string $host = '';

    private string $path = '';

    /** @var CompressionInterface[] */
    private array $compressions = [];

    public function __construct()
    {
        $this->fileSystems = new FileSystems();
        $this->databases = new Databases();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): AppInstance
    {
        $this->name = $name;
        return $this;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function setHost(string $host): AppInstance
    {
        if (str_contains($host, '/')) {
            throw new InvalidArgumentException(sprintf('host %s has invalid character', $host));
        }

        $this->host = $host;
        return $this;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function setPath(string $path): AppInstance
    {
        $this->path = $path;
        return $this;
    }

    public function addDatabaseObject(Database $database): AppInstance
    {
        $this->databases->add($database);
        return $this;
    }

    public function hasFilesystem(string $name): bool
    {
        return $this->fileSystems->has($name);
    }

    public function getFilesystem(string $name): FileSystem
    {
        return $this->fileSystems->get($name);
    }

    public function hasDatabase(string $name): bool
    {
        return $this->databases->has($name);
    }

    public function getDatabase(string $name): Database
    {
        return $this->databases->get($name);
    }

    /**
     * @return Database[]
     */
    public function getDatabases(): array
    {
        return $this->databases->getAll();
    }

    /**
     * @return string[]
     */
    public function getDatabaseNames(): array
    {
        return $this->databases->getDatabaseNames();
    }

    /**
     * @return CompressionInterface[]
     */
    public function getCompressions(): array
    {
        return $this->compressions;
    }

    public function setCompressions(CompressionInterface ...$compressions): AppInstance
    {
        $this->compressions = $compressions;
        return $this;
    }

    public function unsetCompressions(): AppInstance
    {
        $this->setCompressions();
        return $this;
    }
}
