<?php
declare(strict_types=1);

namespace PHPSu\Configuration\RawConfiguration;

use PHPSu\Configuration\RawConfiguration\AbstractClasses\RawNameableDto;

class RawHostDto extends RawNameableDto
{
    /**
     * @var RawConsoleDto
     */
    private $console;
    /**
     * @var RawFilesystemBag
     */
    private $filesystems;
    /**
     * @var RawDatabaseBag
     */
    private $databases;

    public function __construct(string $name, RawConsoleDto $console = null, RawFilesystemBag $filesystems = null, RawDatabaseBag $databases = null)
    {
        parent::__construct($name);
        $this->console = $console ?? new RawConsoleDto();
        $this->filesystems = $filesystems ?? new RawFilesystemBag();
        $this->databases = $databases ?? new RawDatabaseBag();
    }

    public function getConsole(): RawConsoleDto
    {
        return $this->console;
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
        return new static(
            $data['name'] ?? '',
            $data['console'] ?? [],
            $data['filesystems'] ?? [],
            $data['databases'] ?? []
        );
    }
}
