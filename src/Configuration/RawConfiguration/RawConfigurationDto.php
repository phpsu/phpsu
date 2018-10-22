<?php
declare(strict_types=1);

namespace PHPSu\Configuration\RawConfiguration;

class RawConfigurationDto
{
    public function __construct(RawHostBag $hosts = null, RawFilesystemBag $filesystems = null, RawDatabaseBag $databases = null)
    {
        $this->hosts = $hosts ?? new RawHostBag();
        $this->filesystems = $filesystems ?? new RawFilesystemBag();
        $this->databases = $databases ?? new RawDatabaseBag();
    }

    protected $hosts;
    protected $filesystems;
    protected $databases;

    public function getHosts(): RawHostBag
    {
        return $this->hosts;
    }

    public function getFilesystems(): RawFilesystemBag
    {
        return $this->filesystems;
    }

    public function getDatabases(): RawDatabaseBag
    {
        return $this->databases;
    }
}
