<?php
declare(strict_types=1);

namespace PHPSu\Configuration\Dto;

class HostDto extends NameableDto
{
    protected $console;
    protected $filesystems;
    protected $databases;

    public function getConsole(): ConsoleDto
    {
    }

    public function getFilesystems(): FilesystemBag
    {
    }

    public function getDatabases(): DatabaseBag
    {
    }
}
