<?php

declare(strict_types=1);

namespace PHPSu\Config;

/**
 * @internal
 */
trait AddFilesystemTrait
{
    private FileSystems $fileSystems;

    /**
     * @api
     */
    public function addFilesystemObject(FileSystem $fileSystem): self
    {
        $this->fileSystems->add($fileSystem);
        return $this;
    }

    /**
     * @api
     */
    public function addFilesystem(string $name, string $path): FileSystem
    {
        $fileSystem = new FileSystem();
        $fileSystem->setName($name)->setPath($path);
        $this->fileSystems->add($fileSystem);
        return $fileSystem;
    }
}
