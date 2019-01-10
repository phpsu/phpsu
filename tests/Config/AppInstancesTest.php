<?php
declare(strict_types=1);

namespace PHPSu\Tests\Config;

use PHPSu\Config\AppInstance;
use PHPSu\Config\AppInstances;
use PHPUnit\Framework\TestCase;

final class AppInstancesTest extends TestCase
{
    /**
     * @expectedException \Exception
     */
    public function testGetException(): void
    {
        $apps = new AppInstances();
        $apps->get('NameNotInApps');
    }

    public function testGetAll(): void
    {
        $apps = new AppInstances();
        $this->assertSame([], $apps->getAll());
    }

    public function testGetAllOneInstance(): void
    {
        $apps = new AppInstances();
        $name = 'TestInstance';
        $apps->add($instance = (new AppInstance())->setName($name));
        $this->assertSame([$name => $instance], $apps->getAll());
    }
}
