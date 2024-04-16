<?php

declare(strict_types=1);

namespace PHPSu\Tests\Config;

use Exception;
use PHPSu\Config\FileSystems;
use PHPUnit\Framework\TestCase;

final class FileSystemsTest extends TestCase
{
    public function testGetException(): void
    {
        $this->expectException(Exception::class);
        $fileSystems = new FileSystems();
        $this->expectExceptionMessage('Filesystem NameNotInDatabases not found');
        $fileSystems->get('NameNotInDatabases');
    }
}
