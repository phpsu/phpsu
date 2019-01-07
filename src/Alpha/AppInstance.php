<?php
declare(strict_types=1);

namespace PHPSu\Alpha;

final class AppInstance
{
    /** @var string */
    private $name;
    /** @var string */
    private $host = '';
    /** @var string */
    private $path = '';
    /** @var FileSystems */
    private $filesystems;
    /** @var Databases */
    private $databases;

    public function __construct()
    {
        $this->filesystems = new FileSystems();
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

    public function addFilesystem(FileSystem $fileSystem): AppInstance
    {
        $this->filesystems->add($fileSystem);
        return $this;
    }

    public function addDatabase(Database $database): AppInstance
    {
        $this->databases->add($database);
        return $this;
    }

    public function hasFilesystem(string $name): bool
    {
        return $this->filesystems->has($name);
    }

    public function getFilesystem(string $name): FileSystem
    {
        return $this->filesystems->get($name);
    }

    public function hasDatabase(string $name): bool
    {
        return $this->databases->has($name);
    }

    public function getDatabase(string $name): Database
    {
        return $this->databases->get($name);
    }
}