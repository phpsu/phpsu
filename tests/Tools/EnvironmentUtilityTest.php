<?php

declare(strict_types=1);

namespace PHPSu\Tests\Tools;

use PHPSu\Exceptions\CommandExecutionException;
use PHPSu\Tools\EnvironmentUtility;
use PHPUnit\Framework\TestCase;

class EnvironmentUtilityTest extends TestCase
{
    public function testCommandIsInstalled(): void
    {
        $this->assertTrue((new EnvironmentUtility())->isCommandInstalled('echo'));
    }

    public function testCommandIsNotInstalled(): void
    {
        $this->assertFalse((new EnvironmentUtility())->isCommandInstalled('reiguheruh'));
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

    public function testGetInstalledSymfonyConsoleVersion(): void
    {
        $environmentUtility = new EnvironmentUtility();
        $phpsuRootPath = __DIR__ . '/../fixtures/installed/version4.2';
        $environmentUtility->setPhpsuRootPath($phpsuRootPath);
        $this->assertSame($phpsuRootPath, $environmentUtility->getPhpsuRootPath());
        $symfonyConsoleVersion = $environmentUtility->getSymfonyConsoleVersion();
        $this->assertSame('4.2.19992', $symfonyConsoleVersion);
    }

    public function testGetInstalledSymfonyConsoleVersionFixtures(): void
    {
        $environmentUtility = new EnvironmentUtility();
        $environmentUtility->setPhpsuRootPath(__DIR__ . '/../fixtures/installed/empty');
        $this->expectExceptionMessage('could not retrieve package version of symfony/console, not installed?');
        $environmentUtility->getSymfonyConsoleVersion();
    }

    public function testGetInstalledSymfonyProcessVersion(): void
    {
        $environmentUtility = new EnvironmentUtility();
        $environmentUtility->setPhpsuRootPath(__DIR__ . '/../fixtures/installed/version4.2');
        $symfonyProcessVersion = $environmentUtility->getSymfonyProcessVersion();
        $this->assertSame('4.2.19991', $symfonyProcessVersion);
    }

    public function testGetInstalledSymfonyProcessVersionFixturesA(): void
    {
        $environmentUtility = new EnvironmentUtility();
        $environmentUtility->setPhpsuRootPath(__DIR__ . '/../fixtures/installed/empty');
        $this->expectExceptionMessage('could not retrieve package version of symfony/process, not installed?');
        $environmentUtility->getSymfonyProcessVersion();
    }

    public function testGetInstalledSymfonyProcessVersionFixturesB(): void
    {
        $environmentUtility = new EnvironmentUtility();
        $phpsuRootPath = __DIR__ . '/../fixtures/installed/noFile';
        $environmentUtility->setPhpsuRootPath($phpsuRootPath);
        $this->expectExceptionMessageMatches('/failed to open stream\: No such file or directory$/');
        $environmentUtility->getSymfonyProcessVersion();
    }

    public function testGetInstalledSymfonyProcessVersionFixturesC(): void
    {
        $oldErrorReporting = error_reporting();
        error_reporting($oldErrorReporting & ~E_WARNING);
        $environmentUtility = new EnvironmentUtility();
        $phpsuRootPath = __DIR__ . '/../fixtures/installed/noFile';
        $environmentUtility->setPhpsuRootPath($phpsuRootPath);
        $this->expectExceptionMessage('could not retrieve package version of symfony/process, not installed?');
        try {
            $environmentUtility->getSymfonyProcessVersion();
        } finally {
            error_reporting($oldErrorReporting);
        }
    }

    public function testGetInstalledSymfonyProcessVersionFixturesD(): void
    {
        $environmentUtility = new EnvironmentUtility();
        $phpsuRootPath = __DIR__ . '/../fixtures/installed/invalidJson';
        $environmentUtility->setPhpsuRootPath($phpsuRootPath);
        $this->expectExceptionMessage('could not retrieve package version of symfony/process, not installed?');
        $environmentUtility->getSymfonyProcessVersion();
    }
}
