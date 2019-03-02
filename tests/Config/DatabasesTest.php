<?php
declare(strict_types=1);

namespace PHPSu\Tests\Config;

use PHPSu\Config\Databases;
use PHPUnit\Framework\TestCase;

final class DatabasesTest extends TestCase
{
    /**
     * @expectedException \Exception
     */
    public function testGetException()
    {
        $databases = new Databases();
        $databases->get('NameNotInDatabases');
    }
}
