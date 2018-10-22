<?php
declare(strict_types=1);

namespace PHPSu\Configuration\RawConfiguration;

use PHPSu\Configuration\Dto\NameableDto;

class RawHostDto extends NameableDto
{
    protected $console;
    protected $filesystems;
    protected $databases;

    public function getConsole(): RawConsoleDto
    {
    }

    public function getFilesystems(): RawFilesystemBag
    {
    }

    public function getDatabases(): RawDatabaseBag
    {
    }
}
