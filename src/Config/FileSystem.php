<?php

declare(strict_types=1);

namespace PHPSu\Config;

use function array_merge;

/**
 * @api
 */
final class FileSystem implements ConfigElement
{
    private string $name;
    private string $path;
    /** @var string[] */
    private array $excludes = [];

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
     * @return string[]
     */
    public function getExcludes(): array
    {
        return $this->excludes;
    }

    /**
     * @param string[] $excludes
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
