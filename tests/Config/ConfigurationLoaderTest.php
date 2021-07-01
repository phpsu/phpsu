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

    public function setUp(): void
    {
        $this->oldCwd = getcwd();
        chdir(__DIR__ . '/../fixtures');
    }

    public function testGetConfigViaConfigurationLoader(): void
    {
        $configurationLoader = new ConfigurationLoader();
        $expectedConfig = new GlobalConfig();
        $expectedConfig->addFilesystem('var/storage', 'var/storage');
        $expectedConfig->addAppInstance('production', '', 'testProduction');
        $expectedConfig->addAppInstance('local', '', 'testLocal');
        $this->assertEquals($expectedConfig, $configurationLoader->getConfig());
    }

    public function testGetConfigViaConfigurationLoaderWithDifferentName(): void
    {
        $configurationLoader = new ConfigurationLoader('alternative-config.php');
        $expectedConfig = new GlobalConfig();
        $expectedConfig->addFilesystem('var/storage', 'var/storage');
        $expectedConfig->addAppInstance('production', '', 'testProduction');
        $expectedConfig->addAppInstance('local', '', 'testLocal');
        $this->assertEquals($expectedConfig, $configurationLoader->getConfig());
    }

    public function testConfigNotFoundException(): void
    {
        chdir(__DIR__ . '/../fixtures/dir-without-phpsu-config');
        $this->expectExceptionMessageMatches('/.* does not exist/');
        $configurationLoader = new ConfigurationLoader();
        $configurationLoader->getConfig();
    }

    public function tearDown(): void
    {
        chdir($this->oldCwd);
    }
}
