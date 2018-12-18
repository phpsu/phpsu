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
}
