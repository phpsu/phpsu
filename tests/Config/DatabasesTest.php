<?php

declare(strict_types=1);

namespace PHPSu\Tests\Config;

use Exception;
use PHPSu\Config\Databases;
use PHPUnit\Framework\TestCase;

final class DatabasesTest extends TestCase
{
    public function testGetException(): void
    {
        $this->expectException(Exception::class);
        $databases = new Databases();
        $this->expectExceptionMessage('Database NameNotInDatabases not found');
        $databases->get('NameNotInDatabases');
    }
}
