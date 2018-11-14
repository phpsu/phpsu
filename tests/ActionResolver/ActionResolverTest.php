<?php
/**
 * Created by PhpStorm.
 * User: kanti
 * Date: 13.11.18
 * Time: 07:29
 */

namespace PHPSu\Tests\ActionResolver;

use PHPSu\ActionResolver\ActionResolver;
use PHPSu\Actions\ActionList;
use PHPSu\Configuration\ProcessedConfiguration\ProcessedConfigurationDto;
use PHPSu\Configuration\ProcessedConfiguration\ProcessedConsoleDto;
use PHPSu\Configuration\ProcessedConfiguration\ProcessedFilesystemBag;
use PHPSu\Configuration\ProcessedConfiguration\ProcessedFilesystemDto;
use PHPSu\Configuration\ProcessedConfiguration\ProcessedHostBag;
use PHPSu\Configuration\ProcessedConfiguration\ProcessedHostDto;
use PHPSu\Configuration\ProcessedConfiguration\ProcessedOptionBag;
use PHPSu\Core\ApplicationContext;
use PHPUnit\Framework\TestCase;

class ActionResolverTest extends TestCase
{
    /**
     * @dataProvider providerTestResolve
     * @param ProcessedConfigurationDto $processedConfigurationDto
     * @param ActionList $expectedActionList
     */
    public function testResolve(ApplicationContext $applicationContext, ProcessedConfigurationDto $processedConfigurationDto, ActionList $expectedActionList)
    {
        $actionResolver = new ActionResolver($applicationContext);
        $actualActionList = $actionResolver->resolve($processedConfigurationDto);
        $this->assertEquals($expectedActionList, $actualActionList);
    }

    public function providerTestResolve(): \Generator
    {
        $applicationContext = new ApplicationContext();
        $applicationContext->toHost = 'local';
        $applicationContext->fromHost = 'production';
        yield 'empty' => [
            $applicationContext,
            new ProcessedConfigurationDto(
                new ProcessedHostBag(
                    new ProcessedHostDto(
                        'local',
                        new ProcessedConsoleDto('local', 'local'),
                        new ProcessedFilesystemBag(
                            new ProcessedFilesystemDto(
                                'default',
                                'dir',
                                new ProcessedOptionBag(
                                    [
                                        'dir' => 'Frag/*',
                                    ]
                                )
                            )
                        )
                    ),
                    new ProcessedHostDto(
                        'production',
                        new ProcessedConsoleDto(
                            'production',
                            'ssh',
                            new ProcessedOptionBag(
                                [
                                    'host' => 'ssh-hostA',
                                    'port' => 2222,
                                    'user' => 'user',
                                ]
                            )
                        ),
                        new ProcessedFilesystemBag(
                            new ProcessedFilesystemDto(
                                'default',
                                'dir',
                                new ProcessedOptionBag(
                                    [
                                        'dir' => 'Frag2/*',
                                    ]
                                )
                            )
                        )
                    )
                )
            ),
            new ActionList(),
        ];
    }
}
