<?php
declare(strict_types=1);

namespace PHPSu\Tests\Configuration;

use League\Container\Container;
use PHPSu\Configuration\ConfigurationResolver;
use PHPSu\Configuration\ProcessedConfiguration\ProcessedConfigurationDto;
use PHPSu\Configuration\ProcessedConfiguration\ProcessedConsoleDto;
use PHPSu\Configuration\ProcessedConfiguration\ProcessedDatabaseBag;
use PHPSu\Configuration\ProcessedConfiguration\ProcessedDatabaseDto;
use PHPSu\Configuration\ProcessedConfiguration\ProcessedFilesystemBag;
use PHPSu\Configuration\ProcessedConfiguration\ProcessedFilesystemDto;
use PHPSu\Configuration\ProcessedConfiguration\ProcessedHostBag;
use PHPSu\Configuration\ProcessedConfiguration\ProcessedHostDto;
use PHPSu\Configuration\ProcessedConfiguration\ProcessedOptionBag;
use PHPSu\Configuration\RawConfiguration\RawConfigurationDto;
use PHPSu\Configuration\RawConfiguration\RawDatabaseBag;
use PHPSu\Configuration\RawConfiguration\RawDatabaseDto;
use PHPSu\Configuration\RawConfiguration\RawFilesystemBag;
use PHPSu\Configuration\RawConfiguration\RawFilesystemDto;
use PHPSu\Configuration\RawConfiguration\RawHostBag;
use PHPSu\Configuration\RawConfiguration\RawHostDto;
use PHPSu\Configuration\RawConfiguration\RawOptionBag;
use PHPSu\Core\ApplicationContext;
use PHPSu\Tests\TestHelper\ConfigurationTestHelper;
use PHPUnit\Framework\TestCase;
use Spatie\Snapshots\MatchesSnapshots;

class ConfigurationResolverTest extends TestCase
{
    use MatchesSnapshots;

    /**
     * @dataProvider rawConfigurationProvider
     * @param RawConfigurationDto $rawConfiguration
     * @param ProcessedConfigurationDto $expectedProcessedConfiguration
     */
    public function testResolveRawConfigToProcessed(RawConfigurationDto $rawConfiguration, ProcessedConfigurationDto $expectedProcessedConfiguration)
    {
        $configurationResolver = new ConfigurationResolver(new Container(), new ApplicationContext());
        ConfigurationTestHelper::assertIfRawConfigurationDtoIsValid($rawConfiguration);
        $processedConfigurationShould = $configurationResolver->resolveRawConfigToProcessed($rawConfiguration);
        ConfigurationTestHelper::assertIfProcessedConfigurationDtoIsValid($processedConfigurationShould);
        $this->assertEquals($expectedProcessedConfiguration, $processedConfigurationShould);
    }

    public function rawConfigurationProvider()
    {
        yield 'empty config' => [
            new RawConfigurationDto(),
            new ProcessedConfigurationDto(
                new ProcessedHostBag(
                    new ProcessedHostDto(
                        'local',
                        new ProcessedConsoleDto('Default', 'local')
                    )
                )
            ),
        ];
        yield 'config with one Host' => [
            new RawConfigurationDto(
                new RawHostBag(
                    new RawHostDto('testHost')
                )
            ),
            new ProcessedConfigurationDto(
                new ProcessedHostBag(
                    new ProcessedHostDto(
                        'local',
                        new ProcessedConsoleDto('Default', 'local')
                    ),
                    new ProcessedHostDto(
                        'testHost',
                        new ProcessedConsoleDto('Default', 'local')
                    )
                )
            ),
        ];
        yield 'config with one Filesystem' => [
            new RawConfigurationDto(
                null,
                new RawFilesystemBag(
                    new RawFilesystemDto()
                )
            ),
            new ProcessedConfigurationDto(
                new ProcessedHostBag(
                    new ProcessedHostDto(
                        'local',
                        new ProcessedConsoleDto('Default', 'local'),
                        new ProcessedFilesystemBag(
                            new ProcessedFilesystemDto('Default', 'directory')
                        )
                    )
                ),
                new ProcessedFilesystemBag(
                    new ProcessedFilesystemDto('Default', 'directory')
                )
            ),
        ];
        yield 'config with one Database' => [
            new RawConfigurationDto(
                null,
                null,
                new RawDatabaseBag(
                    new RawDatabaseDto()
                )
            ),
            new ProcessedConfigurationDto(
                new ProcessedHostBag(
                    new ProcessedHostDto(
                        'local',
                        new ProcessedConsoleDto('Default', 'local'),
                        null,
                        new ProcessedDatabaseBag(
                            new ProcessedDatabaseDto('Default', 'auto')
                        )
                    )
                ),
                null,
                new ProcessedDatabaseBag(
                    new ProcessedDatabaseDto('Default', 'auto')
                )
            ),
        ];
        yield 'config with one Filesystem in Global and Host' => [
            new RawConfigurationDto(
                new RawHostBag(
                    new RawHostDto(
                        'Production',
                        null,
                        new RawFilesystemBag(
                            new RawFilesystemDto(
                                '',
                                '',
                                new RawOptionBag([
                                    'directory' => 'testing/test',
                                    'include' => '*.mp3',
                                ])
                            )
                        )
                    )
                ),
                new RawFilesystemBag(
                    new RawFilesystemDto(
                        '',
                        '',
                        new RawOptionBag([
                            'directory' => 'testing/test2',
                            'exclude' => '*.mp4',
                        ])
                    )
                )
            ),
            new ProcessedConfigurationDto(
                new ProcessedHostBag(
                    new ProcessedHostDto(
                        'Production',
                        new ProcessedConsoleDto('Default', 'local'),
                        new ProcessedFilesystemBag(
                            new ProcessedFilesystemDto(
                                'Default',
                                'directory',
                                new ProcessedOptionBag([
                                    'directory' => 'testing/test',
                                    'include' => '*.mp3',
                                    'exclude' => '*.mp4',
                                ])
                            )
                        )
                    ),
                    new ProcessedHostDto(
                        'local',
                        new ProcessedConsoleDto('Default', 'local'),
                        new ProcessedFilesystemBag(
                            new ProcessedFilesystemDto(
                                'Default',
                                'directory',
                                new ProcessedOptionBag([
                                    'directory' => 'testing/test2',
                                    'exclude' => '*.mp4',
                                ])
                            )
                        )
                    )
                ),
                new ProcessedFilesystemBag(
                    new ProcessedFilesystemDto(
                        'Default',
                        'directory',
                        new ProcessedOptionBag([
                            'directory' => 'testing/test2',
                            'exclude' => '*.mp4',
                        ])
                    )
                )
            ),
        ];
    }
}
