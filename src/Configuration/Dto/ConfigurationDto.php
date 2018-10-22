<?php
declare(strict_types=1);

namespace PHPSu\Configuration\Dto;

class ConfigurationDto
{
    public function __construct(HostBag $hosts = null, FilesystemBag $filesystems = null, DatabaseBag $databases = null)
    {
        $this->hosts = $hosts ?? new HostBag();
        $this->filesystems = $filesystems ?? new FilesystemBag();
        $this->databases = $databases ?? new DatabaseBag();
    }

    protected $hosts;
    protected $filesystems;
    protected $databases;

    public function getHosts(): HostBag
    {
        return $this->hosts;
    }

    public function getFilesystems(): FilesystemBag
    {
        return $this->filesystems;
    }

    public function getDatabases(): DatabaseBag
    {
        return $this->databases;
    }
}
