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
    public function testCommandIsInstalled(): void
    {
        $this->assertTrue((new EnvironmentUtility())->isCommandInstalled('echo'));
    }

    public function testCommandIsNotInstalled(): void
    {
        $this->assertFalse((new EnvironmentUtility())->isCommandInstalled('reiguheruh'));
    }

    /**
     * @throws CommandExecutionException|ReflectionException
     */
    public function testGetInstalledRsyncVersionNotInstalled(): void
    {
        $environmentUtility = $this->getEnvironmentUtility(null);
        static::expectExceptionMessage('Result of rsync --version was empty');
        $environmentUtility->getRsyncVersion();
    }

    /**
     * @throws CommandExecutionException|ReflectionException
     */
    public function testGetInstalledSshVersionNotInstalled(): void
    {
        $environmentUtility = $this->getEnvironmentUtility(ShellBuilder::command('efwdwdwd'));
        static::expectExceptionMessage('Result of ssh -V was empty');
        $environmentUtility->getSshVersion();
    }

    /**
     * @throws CommandExecutionException|ReflectionException
     */
    public function testGetInstalledSshVersionInstalled(): void
    {
        $echo = ShellBuilder::command('echo')->addArgument('OpenSSH_7.9p1');
        $environmentUtility = $this->getEnvironmentUtility($echo);
        $version = $environmentUtility->getSshVersion();
        $this->assertEquals('7.9p1', $version);
    }

    /**
     * @throws CommandExecutionException|ReflectionException
     */
    public function testGetInstalledMysqlVersionNotInstalled(): void
    {
        $environmentUtility = $this->getEnvironmentUtility(null);
        static::expectExceptionMessage('Result of mysqldump -V was empty');
        $environmentUtility->getMysqlDumpVersion();
    }

    /**
     * @throws CommandExecutionException|ReflectionException
     */
    public function testGetInstalledMysqlVersionInstalled(): void
    {
        $echo = ShellBuilder::command('echo')->addArgument('mysqldump  Ver 10.17 Distrib 10.3.23-MariaDB');
        $environmentUtility = $this->getEnvironmentUtility($echo);
        $version = $environmentUtility->getMysqlDumpVersion();
        $this->assertEquals([
            'mysqlVersion' => '10.3.23',
            'dumpVersion' => '10.17',
        ], $version);
    }

    /**
     * @throws CommandExecutionException
     * @throws ReflectionException
     * @throws ShellBuilderException
     */
    public function testGetInstalledRsyncVersionInstalled(): void
    {
        $environmentUtility = $this->getEnvironmentUtility(ShellBuilder::command('echo')->addArgument('rsync  version 3.1.3'));
        $version = $environmentUtility->getRsyncVersion();
        $this->assertEquals('3.1.3', $version);
    }

    /**
     * @param ShellInterface|null $command
     * @return EnvironmentUtility
     * @throws ReflectionException
     */
    private function getEnvironmentUtility(?ShellInterface $command): EnvironmentUtility
    {
        $executor = new class extends CommandExecutor {
            public static $command;

            public function runCommand(ShellInterface $command): Process
            {
                $process = Process::fromShellCommandline((string)self::$command ?? '');
                $process->run();
                return $process;
            }
        };
        $executor::$command = $command;
        $environmentUtility = new EnvironmentUtility();
        $reflection = new ReflectionClass($environmentUtility);
        $property = $reflection->getProperty('commandExecutor');
        $property->setAccessible(true);
        $property->setValue($environmentUtility, $executor);
        return $environmentUtility;
    }

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

    public function testGetInstalledRsyncVersionOrExpectExceptionIfNotInstalled(): void
    {
        if ((new EnvironmentUtility())->isRsyncInstalled()) {
            $rsyncVersion = (new EnvironmentUtility())->getRsyncVersion();
            $this->assertSame(1, version_compare($rsyncVersion, '2.0.0'));
        } else {
            $this->expectException(CommandExecutionException::class);
            (new EnvironmentUtility())->getRsyncVersion();
        }
    }

    public function testGetInstalledSshVersionOrExpectExceptionIfNotInstalled(): void
    {
        if ((new EnvironmentUtility())->isSshInstalled()) {
            $sshVersion = (new EnvironmentUtility())->getSshVersion();
            $this->assertSame(1, version_compare($sshVersion, '3.0.0'));
        } else {
            $this->expectException(CommandExecutionException::class);
            (new EnvironmentUtility())->getSshVersion();
        }
    }

    public function testGetInstalledMysqldumpVersionOrExpectExceptionIfNotInstalled(): void
    {
        if ((new EnvironmentUtility())->isMysqlDumpInstalled()) {
            $mysqldumpVersion = (new EnvironmentUtility())->getMysqlDumpVersion();
            $this->assertSame(1, version_compare($mysqldumpVersion['mysqlVersion'], '5.0.0'));
            $this->assertSame(1, version_compare($mysqldumpVersion['dumpVersion'], '5.0.0'));
        } else {
            $this->expectException(CommandExecutionException::class);
            (new EnvironmentUtility())->getMysqlDumpVersion();
        }
    }
}
