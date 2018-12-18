<?php
declare(strict_types=1);

namespace PHPSu\Alpha;

final class FileSystems
{
    /** @var FileSystem[] */
    private $fileSystems = [];

    public function add(Filesystem $filesystem): void
    {
        $this->fileSystems[$filesystem->getName()] = $filesystem;
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
            throw new \Exception(sprintf('Filesystem %s not found', $name));
        }
        return $this->fileSystems[$name];
    }
}
