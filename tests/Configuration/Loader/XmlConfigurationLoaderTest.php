<?php
declare(strict_types=1);

namespace PHPSu\Tests\Configuration\Loader;

use PHPSu\Configuration\Loader\XmlConfigurationLoader;
use PHPSu\Tests\TestHelper\ConfigurationTestHelper;
use PHPUnit\Framework\TestCase;

class XmlConfigurationLoaderTest extends TestCase
{
    /**
     * @dataProvider configFileProvider
     * @param string $configFile
     */
    public function testGetRawConfiguration(string $configFile)
    {
        $loader = new XmlConfigurationLoader(['file' => $configFile]);
        $rawConfiguration = $loader->getRawConfiguration();
        ConfigurationTestHelper::assertIfRawConfigurationDtoIsValid($rawConfiguration);
    }

    public function configFileProvider()
    {
        yield 'empty config File' => [__DIR__ . '/../../Fixtures/Configuration/emptyConfiguration.xml'];
    }
}
