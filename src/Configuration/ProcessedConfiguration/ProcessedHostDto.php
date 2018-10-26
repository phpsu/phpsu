<?php
declare(strict_types=1);

namespace PHPSu\Configuration\ProcessedConfiguration;

use PHPSu\Configuration\ProcessedConfiguration\AbstractClasses\ProcessedNameableDto;

class ProcessedHostDto extends ProcessedNameableDto
{
    /**
     * @var ProcessedConsoleDto
     */
    private $console;
    /**
     * @var ProcessedFilesystemBag
     */
    private $filesystems;
    /**
     * @var ProcessedDatabaseBag
     */
    private $databases;

    public function __construct(string $name, ProcessedConsoleDto $console, ProcessedFilesystemBag $filesystems = null, ProcessedDatabaseBag $databases = null)
    {
        parent::__construct($name);
        $this->console = $console;
        $this->filesystems = $filesystems ?? new ProcessedFilesystemBag();
        $this->databases = $databases ?? new ProcessedDatabaseBag();
    }

    public function getConsole(): ProcessedConsoleDto
    {
        return $this->console;
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
        return new static(
            $data['name'] ?? '',
            $data['console'] ?? [],
            $data['filesystems'] ?? [],
            $data['databases'] ?? []
        );
    }
}
