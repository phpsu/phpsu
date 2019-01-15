<?php
declare(strict_types=1);

namespace PHPSu\Tests\Config;

use PHPSu\Config\ConfigurationLoader;
use PHPSu\Config\GlobalConfig;
use PHPUnit\Framework\TestCase;

final class ConfigurationLoaderTest extends TestCase
{
    /** @var string */
    private $oldCwd;

    public function setUp()
    {
        $this->oldCwd = getcwd();
        chdir(__DIR__ . '/../fixtures');
    }

    public function testGetConfig(): void
    {
        $configurationLoader = new ConfigurationLoader();
        $this->assertEquals(new GlobalConfig(), $configurationLoader->getConfig());
    }

    public function tearDown()
    {
        chdir($this->oldCwd);
    }
}
