<?php
declare(strict_types=1);

namespace PHPSu\Configuration\ProcessedConfiguration;

class ProcessedConfigurationDto
{
    /**
     * @var ProcessedHostBag
     */
    protected $hosts;
    /**
     * @var ProcessedFilesystemBag
     */
    protected $filesystems;
    /**
     * @var ProcessedDatabaseBag
     */
    protected $databases;

    public function __construct(ProcessedHostBag $hosts = null, ProcessedFilesystemBag $filesystems = null, ProcessedDatabaseBag $databases = null)
    {
        $this->hosts = $hosts ?? new ProcessedHostBag();
        $this->filesystems = $filesystems ?? new ProcessedFilesystemBag();
        $this->databases = $databases ?? new ProcessedDatabaseBag();
    }

    public function getHosts(): ProcessedHostBag
    {
        return $this->hosts;
    }

    public function getFilesystems(): ProcessedFilesystemBag
    {
        return $this->filesystems;
    }

    public function getDatabases(): ProcessedDatabaseBag
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
