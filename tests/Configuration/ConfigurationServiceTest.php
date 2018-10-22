<?php
declare(strict_types=1);

namespace PHPSu\Tests\Configuration;

use PHPSu\Configuration\ConfigurationService;
use PHPSu\Configuration\Dto\ConfigurationDto;
use PHPSu\Configuration\Dto\HostBag;
use PHPSu\Configuration\Dto\HostDto;
use PHPSu\Tests\TestHelper\ConfigurationTestHelper;
use PHPUnit\Framework\TestCase;

class ConfigurationServiceTest extends TestCase
{
    public function arrayToObjectsProvider()
    {
        yield 'empty Config' => [new ConfigurationService(), []];
    }

    /**
     * @dataProvider arrayToObjectsProvider
     * @param ConfigurationService $configurationService
     * @param array $configuration
     */
    public function testArrayToObjects(ConfigurationService $configurationService, array $configuration)
    {
        $configurationDto = $configurationService->arrayToObject($configuration);
        ConfigurationTestHelper::assertIfConfigurationDtoIsValid($configurationDto);
    }

    public function overlayMethodProvider()
    {
        yield 'Empty Configuration' => [new ConfigurationService(), new ConfigurationDto()];
        yield 'Configuration With Host' => [new ConfigurationService(), new ConfigurationDto(new HostBag(new HostDto('test1')))];
        yield 'Configuration With two Hosts' => [new ConfigurationService(), new ConfigurationDto(new HostBag(new HostDto('test2'), new HostDto('test3')))];
    }

    /**
     * @dataProvider overlayMethodProvider
     * @param ConfigurationService $configurationService
     * @param ConfigurationDto $configuration
     */
    public function testOverlayMethod(ConfigurationService $configurationService, ConfigurationDto $configuration)
    {
        ConfigurationTestHelper::assertIfConfigurationDtoIsValid($configuration);
        $overlayedConfiguration = $configurationService->overlayConfiguration($configuration);
        ConfigurationTestHelper::assertIfConfigurationDtoIsValid($overlayedConfiguration);

        foreach ($configuration->getHosts() as $host) {
            foreach ($host->getDatabases() as $hostDatabase) {
                $defaultDatabase = $configuration->getFilesystems()[$hostDatabase->getName()];
                foreach ($defaultDatabase->getOptions() as $key => $value) {
                    if (isset($hostDatabase->getOptions()[$key])) {
                        $value = $hostDatabase->getOptions()[$key];
                    }
                    $overlayedValue = $overlayedConfiguration->getHosts()[$host->getName()]->getDatabases()[$hostDatabase->getName()]->getOptions()[$key];
                    $this->assertSame($value, $overlayedValue);
                }
            }
            foreach ($host->getFilesystems() as $hostFilesystem) {
                $defaultFilesystem = $configuration->getFilesystems()[$hostFilesystem->getName()];
                foreach ($defaultFilesystem->getOptions() as $key => $value) {
                    if (isset($hostFilesystem->getOptions()[$key])) {
                        $value = $hostFilesystem->getOptions()[$key];
                    }
                    $overlayedValue = $overlayedConfiguration->getHosts()[$host->getName()]->getFilesystems()[$hostFilesystem->getName()]->getOptions()[$key];
                    $this->assertSame($value, $overlayedValue);
                }
            }
        }
    }
}
