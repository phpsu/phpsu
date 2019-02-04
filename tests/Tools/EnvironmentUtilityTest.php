<?php declare(strict_types=1);

namespace PHPSu\Tests\Tools;

use PHPSu\Tools\EnvironmentUtility;
use PHPUnit\Framework\TestCase;

class EnvironmentUtilityTest extends TestCase
{
    public function testCommandIsInstalled(): void
    {
        $this->assertEquals(true, (new EnvironmentUtility())->isCommandInstalled('echo'));
    }

    public function testCommandIsNotInstalled(): void
    {
        $this->assertEquals(false, (new EnvironmentUtility())->isCommandInstalled('reiguheruh'));
    }
}
