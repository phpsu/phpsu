<?php
declare(strict_types=1);

namespace PHPSu\Tests\Configuration\Loader;

use PHPSu\Configuration\Loader\XmlConfigurationLoader;
use PHPSu\Tests\TestHelper\ConfigurationTestHelper;
use PHPUnit\Framework\TestCase;
use Spatie\Snapshots\MatchesSnapshots;

class XmlConfigurationLoaderTest extends TestCase
{
    use MatchesSnapshots;

    /**
     * @dataProvider configFileProvider
     * @param string $configurationFile
     */
    public function testGetRawConfiguration(string $configurationFile)
    {
        $loader = new XmlConfigurationLoader(['file' => $configurationFile]);
        $rawConfiguration = $loader->getRawConfiguration();
        ConfigurationTestHelper::assertIfRawConfigurationDtoIsValid($rawConfiguration);
        $this->assertMatchesSnapshot($rawConfiguration);
    }

    public function configFileProvider(): \Generator
    {
        yield 'empty config File' => [__DIR__ . '/../../Fixtures/Configuration/emptyConfiguration.xml'];
        yield 'Full config File' => [__DIR__ . '/../../Fixtures/Configuration/FullConfiguration.xml'];
    }
}
