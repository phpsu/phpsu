<?php declare(strict_types=1);

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
        $symfonyConsoleVersion = (new EnvironmentUtility())->getSymfonyConsoleVersion();
        $this->assertSame(1, version_compare($symfonyConsoleVersion, '3.0.0'));
    }

    public function testGetInstalledSymfonyProcessVersion()
    {
        $symfonyConsoleVersion = (new EnvironmentUtility())->getSymfonyProcessVersion();
        $this->assertSame(1, version_compare($symfonyConsoleVersion, '3.0.0'));
    }
}
