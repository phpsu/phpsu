<?php
declare(strict_types=1);

namespace PHPSu\Config;

trait AddFilesystemTrait
{
    /** @var FileSystems */
    private $fileSystems;

    public function addFilesystemObject(FileSystem $fileSystem): self
    {
        $this->fileSystems->add($fileSystem);
        return $this;
    }

    public function addFilesystem(string $name, string $path): FileSystem
    {
        $fileSystem = new FileSystem();
        $fileSystem->setName($name)->setPath($path);
        $this->fileSystems->add($fileSystem);
        return $fileSystem;
    }
}
