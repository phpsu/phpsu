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

    /**
     * @param string|array $excludes
     * @return FileSystem
     */
    public function addExcludes($excludes): FileSystem
    {
        if (is_array($excludes)) {
            $this->excludes = array_merge($this->excludes, $excludes);
        } else {
            $this->excludes[] = $excludes;
        }
        return $this;
    }

    /**
     * @param string $exclude
     * @return FileSystem
     * @deprecated Use $this->addExcludes();
     */
    public function addExclude(string $exclude): FileSystem
    {
        $this->addExcludes($exclude);
        return $this;
    }
}
