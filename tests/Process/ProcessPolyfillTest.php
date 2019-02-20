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

    public function testSuccessfulProcessCommandOutput(): void
    {
        $result = (new CommandExecutor())->runCommand('echo hi');
        $this->assertEquals($result->getOutput(), 'hi' . PHP_EOL, 'Executor output was correct: hi');
        $this->assertEmpty($result->getErrorOutput(), 'Executor error output was correctly empty');
    }

    public function testErrorProcessCommandOutput(): void
    {
        $result = (new CommandExecutor())->runCommand('oijewfoijwfj');
        $this->assertEmpty($result->getOutput(), 'Executor output was correctly empty');
        $this->assertNotEmpty($result->getErrorOutput(), 'Executor error output was correctly not empty');
    }

    public function testNewProcessForDifferentVersions(): void
    {
        $symfonyVersion = (new EnvironmentUtility())->getSymfonyProcessVersion();
        if (version_compare($symfonyVersion, '3.4.0', '<')) {
            $process = new Process('echo');
            $this->assertInstanceOf(Process::class, $process, 'Successfully created Process');
            $this->expectException(CommandExecutionException::class);
            new Process([]);
        }
        if (version_compare($symfonyVersion, '3.4.0', '>=')) {
            $process = new Process([]);
            $this->assertInstanceOf(Process::class, $process, 'Successfully created Process');
        }
        if (version_compare($symfonyVersion, '4.2.0', '>=')) {
            $this->expectException(CommandExecutionException::class);
            new Process('echo');
        }
    }
}
