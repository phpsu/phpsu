<?php
declare(strict_types=1);

namespace PHPSu\Config;

final class FileSystem
{
    /** @var string */
    private $name;
    /** @var string */
    private $path;
    /** @var string[] */
    private $excludes = [];

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): FileSystem
    {
        $this->name = $name;
        return $this;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function setPath(string $path): FileSystem
    {
        $this->path = $path;
        return $this;
    }

    public function getExcludes(): array
    {
        return $this->excludes;
    }

    public function addExcludes(array $excludes): FileSystem
    {
        $this->excludes = array_merge($this->excludes, $excludes);
        return $this;
    }

    public function addExclude(string $exclude): FileSystem
    {
        $this->excludes[] = $exclude;
        return $this;
    }
}
