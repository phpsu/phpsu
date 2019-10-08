<?php

declare(strict_types=1);

namespace PHPSu\Tests\Config;

use PHPSu\Config\FileSystems;
use PHPUnit\Framework\TestCase;

final class FileSystemsTest extends TestCase
{
    /**
     * @expectedException \Exception
     */
    public function testGetException()
    {
        $fileSystems = new FileSystems();
        $this->expectExceptionMessage('Filesystem NameNotInDatabases not found');
        $fileSystems->get('NameNotInDatabases');
    }
}
