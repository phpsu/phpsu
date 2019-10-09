<?php

declare(strict_types=1);

namespace PHPSu\Tests\Tools;

use PHPSu\Exceptions\CommandExecutionException;
use PHPSu\Tools\EnvironmentUtility;
use PHPUnit\Framework\TestCase;

class EnvironmentUtilityTest extends TestCase
{
    public function testCommandIsInstalled()
    {
        $this->assertSame(true, (new EnvironmentUtility())->isCommandInstalled('echo'));
    }

    public function testCommandIsNotInstalled()
    {
        $this->assertSame(false, (new EnvironmentUtility())->isCommandInstalled('reiguheruh'));
    }

    public function testGetInstalledRsyncVersionOrExpectExceptionIfNotInstalled()
    {
        if ((new EnvironmentUtility())->isRsyncInstalled()) {
            $rsyncVersion = (new EnvironmentUtility())->getRsyncVersion();
            $this->assertSame(1, version_compare($rsyncVersion, '2.0.0'));
        } else {
            $this->expectException(CommandExecutionException::class);
            (new EnvironmentUtility())->getRsyncVersion();
        }
    }

    public function testGetInstalledSshVersionOrExpectExceptionIfNotInstalled()
    {
        if ((new EnvironmentUtility())->isSshInstalled()) {
            $sshVersion = (new EnvironmentUtility())->getSshVersion();
            $this->assertSame(1, version_compare($sshVersion, '3.0.0'));
        } else {
            $this->expectException(CommandExecutionException::class);
            (new EnvironmentUtility())->getSshVersion();
        }
    }

    public function testGetInstalledMysqldumpVersionOrExpectExceptionIfNotInstalled()
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

    public function testGetInstalledSymfonyConsoleVersion()
    {
        $environmentUtility = new EnvironmentUtility();
        $phpsuRootPath = __DIR__ . '/../fixtures/installed/version4.2';
        $environmentUtility->setPhpsuRootPath($phpsuRootPath);
        $this->assertSame($phpsuRootPath, $environmentUtility->getPhpsuRootPath());
        $symfonyConsoleVersion = $environmentUtility->getSymfonyConsoleVersion();
        $this->assertSame('4.2.19992', $symfonyConsoleVersion);
    }

    public function testGetInstalledSymfonyConsoleVersionFixtures()
    {
        $environmentUtility = new EnvironmentUtility();
        $environmentUtility->setPhpsuRootPath(__DIR__ . '/../fixtures/installed/empty');
        $this->expectExceptionMessage('could not retrieve package version of symfony/console, not installed?');
        $environmentUtility->getSymfonyConsoleVersion();
    }

    public function testGetInstalledSymfonyProcessVersion()
    {
        $environmentUtility = new EnvironmentUtility();
        $environmentUtility->setPhpsuRootPath(__DIR__ . '/../fixtures/installed/version4.2');
        $symfonyProcessVersion = $environmentUtility->getSymfonyProcessVersion();
        $this->assertSame('4.2.19991', $symfonyProcessVersion);
    }

    public function testGetInstalledSymfonyProcessVersionFixturesA()
    {
        $environmentUtility = new EnvironmentUtility();
        $environmentUtility->setPhpsuRootPath(__DIR__ . '/../fixtures/installed/empty');
        $this->expectExceptionMessage('could not retrieve package version of symfony/process, not installed?');
        $environmentUtility->getSymfonyProcessVersion();
    }

    public function testGetInstalledSymfonyProcessVersionFixturesB()
    {
        $environmentUtility = new EnvironmentUtility();
        $phpsuRootPath = __DIR__ . '/../fixtures/installed/noFile';
        $environmentUtility->setPhpsuRootPath($phpsuRootPath);
        $this->expectExceptionMessageRegExp('/failed to open stream\: No such file or directory$/');
        $environmentUtility->getSymfonyProcessVersion();
    }

    public function testGetInstalledSymfonyProcessVersionFixturesC()
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

    public function testGetInstalledSymfonyProcessVersionFixturesD()
    {
        $environmentUtility = new EnvironmentUtility();
        $phpsuRootPath = __DIR__ . '/../fixtures/installed/invalidJson';
        $environmentUtility->setPhpsuRootPath($phpsuRootPath);
        $this->expectExceptionMessage('could not retrieve package version of symfony/process, not installed?');
        $environmentUtility->getSymfonyProcessVersion();
    }
}
