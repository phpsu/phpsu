<?php

declare(strict_types=1);

namespace PHPSu\Tests\Tools;

use PHPSu\Exceptions\CommandExecutionException;
use PHPSu\Process\CommandExecutor;
use PHPSu\Process\Process;
use PHPSu\ShellCommandBuilder\Exception\ShellBuilderException;
use PHPSu\ShellCommandBuilder\ShellBuilder;
use PHPSu\ShellCommandBuilder\ShellInterface;
use PHPSu\Tools\EnvironmentUtility;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionException;

final class EnvironmentUtilityTest extends TestCase
{
    /**
     * @throws ReflectionException
     */
    public function testGetInstalledPackageVersionAutoloadDirForComposer20(): void
    {
        $environmentUtility = new EnvironmentUtility();
        $reflection = new ReflectionClass($environmentUtility);
        $property = $reflection->getProperty('phpsuRootPath');
        $property->setAccessible(true);
        $property->setValue($environmentUtility, __DIR__ . '/../fixtures/installed/autoload/vendor/phpsu/phpsu');

        $version = $environmentUtility->getInstalledPackageVersion('phpsu/phpsu');
        static::assertEquals('1.2.3', $version);
    }

    /**
     * @throws ReflectionException
     */
    public function testGetInstalledPackageVersionInvalidJson(): void
    {
        $environmentUtility = new EnvironmentUtility();
        $reflection = new ReflectionClass($environmentUtility);
        $property = $reflection->getProperty('phpsuRootPath');
        $property->setAccessible(true);
        $property->setValue($environmentUtility, __DIR__ . '/../fixtures/installed/invalidJson');
        static::assertNull($environmentUtility->getInstalledPackageVersion('phpsu/phpsu'));
    }
}
