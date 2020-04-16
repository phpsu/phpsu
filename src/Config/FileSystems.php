<?php

declare(strict_types=1);

namespace PHPSu\Config;

use Exception;

final class FileSystems
{
    /** @var FileSystem[] */
    private $fileSystems = [];

    /**
     * @param FileSystem $fileSystem
     * @return void
     */
    public function add(FileSystem $fileSystem): void
    {
        $this->fileSystems[$fileSystem->getName()] = $fileSystem;
    }

    /**
     * @return FileSystem[]
     */
    public function getAll(): array
    {
        return $this->fileSystems;
    }

    public function has(string $name): bool
    {
        return isset($this->fileSystems[$name]);
    }

    public function get(string $name): FileSystem
    {
        if (!isset($this->fileSystems[$name])) {
            throw new Exception(sprintf('Filesystem %s not found', $name));
        }
        return $this->fileSystems[$name];
    }
}
