<?php declare(strict_types=1);

namespace PHPSu\Tests\Tools;

use PHPSu\Exceptions\CommandExecutionException;
use PHPSu\Tools\EnvironmentUtility;
use PHPUnit\Framework\TestCase;

class EnvironmentUtilityTest extends TestCase
{
    public function testCommandIsInstalled(): void
    {
        $this->assertEquals(true, (new EnvironmentUtility())->isCommandInstalled('echo'));
    }

    public function testCommandIsNotInstalled(): void
    {
        $this->assertEquals(false, (new EnvironmentUtility())->isCommandInstalled('reiguheruh'));
    }

    public function testGetInstalledRsyncVersionOrExpectExceptionIfNotInstalled(): void
    {
        if ((new EnvironmentUtility())->isRsyncInstalled()) {
            $rsyncVersion = (new EnvironmentUtility())->getRsyncVersion();
            $this->assertEquals(1, version_compare($rsyncVersion, '2.0.0'));
        } else {
            $this->expectException(CommandExecutionException::class);
            (new EnvironmentUtility())->getRsyncVersion();
        }
    }

    public function testGetInstalledSshVersionOrExpectExceptionIfNotInstalled(): void
    {
        if ((new EnvironmentUtility())->isSshInstalled()) {
            $sshVersion = (new EnvironmentUtility())->getSshVersion();
            $this->assertEquals(1, version_compare($sshVersion, '7.0.0'));
        } else {
            $this->expectException(CommandExecutionException::class);
            (new EnvironmentUtility())->getSshVersion();
        }
    }

    public function testGetInstalledMysqldumpVersionOrExpectExceptionIfNotInstalled(): void
    {
        if ((new EnvironmentUtility())->isMysqlDumpInstalled()) {
            $mysqldumpVersion = (new EnvironmentUtility())->getMysqlDumpVersion();
            $this->assertEquals(1, version_compare($mysqldumpVersion['mysqlVersion'], '5.0.0'));
            $this->assertEquals(1, version_compare($mysqldumpVersion['dumpVersion'], '5.0.0'));
        } else {
            $this->expectException(CommandExecutionException::class);
            (new EnvironmentUtility())->getMysqlDumpVersion();
        }
    }

    public function testGetInstalledSymfonyConsoleVersion(): void
    {
        $symfonyConsoleVersion = (new EnvironmentUtility())->getSymfonyConsoleVersion();
        $this->assertEquals(1, version_compare($symfonyConsoleVersion, '3.0.0'));
    }

    public function testGetInstalledSymfonyProcessVersion(): void
    {
        $symfonyConsoleVersion = (new EnvironmentUtility())->getSymfonyProcessVersion();
        $this->assertEquals(1, version_compare($symfonyConsoleVersion, '3.0.0'));
    }
}
