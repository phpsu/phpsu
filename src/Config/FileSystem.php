<?php

declare(strict_types=1);

namespace PHPSu\Config;

use function array_merge;

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

    /**
     * @return array<string>
     */
    public function getExcludes(): array
    {
        return $this->excludes;
    }

    /**
     * @param array<string> $excludes
     * @return FileSystem
     * @return FileSystem
     */
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
