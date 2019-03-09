<?php
declare(strict_types=1);

namespace PHPSu\Tests\Process;

use PHPSu\Exceptions\CommandExecutionException;
use PHPSu\Process\CommandExecutor;
use PHPSu\Process\Process;
use PHPSu\Tools\EnvironmentUtility;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Exception\ProcessFailedException;

class ProcessPolyfillTest extends TestCase
{

    public function testSuccessfulProcessCommandOutput()
    {
        $result = (new CommandExecutor())->runCommand('echo hi');
        $this->assertSame($result->getOutput(), 'hi' . PHP_EOL, 'Executor output was correct: hi');
        $this->assertEmpty($result->getErrorOutput(), 'Executor error output was correctly empty');
    }

    public function testErrorProcessCommandOutput()
    {
        $result = (new CommandExecutor())->runCommand('oijewfoijwfj');
        $this->assertEmpty($result->getOutput(), 'Executor output was correctly empty');
        $this->assertNotEmpty($result->getErrorOutput(), 'Executor error output was correctly not empty');
    }

    public function testNewProcessSymfonyOlder3dot4()
    {
        if (version_compare((new EnvironmentUtility())->getSymfonyProcessVersion(), '3.4.0', '<')) {
            $process = new Process('echo');
            $this->assertInstanceOf(Process::class, $process, 'Successfully created Process');
            $this->expectException(CommandExecutionException::class);
            $this->expectExceptionMessage('Support for arrays as commandline-argument is not supported in symfony < 3.4.0');
            new Process([]);
        }
        $this->markTestSkipped('Installed version is not older than 3.4.0');
    }

    public function testNewProcessSymfonyNewer3dot4()
    {
        if (version_compare((new EnvironmentUtility())->getSymfonyProcessVersion(), '3.4.0', '>=')) {
            $process = new Process([]);
            $this->assertInstanceOf(Process::class, $process, 'Successfully created Process');
            return;
        }
        $this->markTestSkipped('Installed version is not newer than 3.4.0');
    }

    public function testNewProcessSymfonyNewer4dot2()
    {
        if (version_compare((new EnvironmentUtility())->getSymfonyProcessVersion(), '4.2.0', '>=')) {
            $this->expectException(CommandExecutionException::class);
            $this->expectExceptionMessage('Support for strings as commandline-argument is not supported in symfony >= 4.2.0');
            new Process('echo');
        }
        $this->markTestSkipped('Installed version is not newer than 4.2.0');
    }
}
