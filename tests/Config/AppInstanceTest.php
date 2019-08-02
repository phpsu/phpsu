<?php
declare(strict_types=1);

namespace PHPSu\Tests\Config;

use PHPSu\Config\AppInstance;
use PHPUnit\Framework\TestCase;

final class AppInstanceTest extends TestCase
{
    public function testSetHostException()
    {
        $apps = new AppInstance();
        $this->expectExceptionMessage('host incorrect/Host has invalid character');
        $apps->setHost('incorrect/Host');
    }
}
