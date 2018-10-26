<?php
declare(strict_types=1);

namespace PHPSu\Configuration\RawConfiguration;

class RawConfigurationDto
{
    /**
     * @var RawHostBag
     */
    protected $hosts;
    /**
     * @var RawFilesystemBag
     */
    protected $filesystems;
    /**
     * @var RawDatabaseBag
     */
    protected $databases;

    public function __construct(RawHostBag $hosts = null, RawFilesystemBag $filesystems = null, RawDatabaseBag $databases = null)
    {
        $this->hosts = $hosts ?? new RawHostBag();
        $this->filesystems = $filesystems ?? new RawFilesystemBag();
        $this->databases = $databases ?? new RawDatabaseBag();
    }

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

    public static function __set_state(array $data)
    {
        return new self(
            $data['hosts'] ?? null,
            $data['filesystems'] ?? null,
            $data['databases'] ?? null
        );
    }
}
