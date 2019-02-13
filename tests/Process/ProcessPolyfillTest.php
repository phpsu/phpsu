<?php
declare(strict_types=1);

namespace PHPSu\Tests\Process;

use PHPSu\Exceptions\CommandExecutionException;
use PHPSu\Process\CommandExecutor;
use PHPSu\Process\Process;
use PHPSu\Tools\EnvironmentUtility;
use PHPUnit\Framework\TestCase;

class ProcessPolyfillTest extends TestCase
{

    public function testSuccessfulProcessCommandOutput(): void
    {
        $result = (new CommandExecutor())->executeDirectly('echo hi');
        $this->assertIsArray($result, 'command executor returned array successfully');
        $this->assertCount(3, $result, 'result contains the correct amount of items');
        $this->assertEquals($result[0], 'hi' . PHP_EOL, 'Executor output was correct: hi');
        $this->assertEmpty($result[1], 'Executor error output was correctly empty');
    }

    public function testErrorProcessCommandOutput(): void
    {
        $result = (new CommandExecutor())->executeDirectly('oijewfoijwfj');
        $this->assertIsArray($result, 'command executor returned array successfully');
        $this->assertCount(3, $result, 'result contains the correct amount of items');
        $this->assertEmpty($result[0], 'Executor output was correctly empty');
        $this->assertNotEmpty($result[1], 'Executor error output was correctly not empty');
    }

    public function testThrowErrorProcessCommand(): void
    {
        $this->expectException(CommandExecutionException::class);
        (new CommandExecutor())->executeDirectly('oijewfoijwfj', true);
    }

    public function testNewProcessForDifferentVersions(): void
    {
        $symfonyVersion = (new EnvironmentUtility())->getSymfonyProcessVersion();
        if (version_compare($symfonyVersion, '3.4.0', 'lt')) {
            $process = new Process('echo', null, null, null, 60);
            $this->assertInstanceOf(Process::class, $process, 'Successfully created Process');
            $this->expectException(CommandExecutionException::class);
            new Process([], null, null, null, 60);
        }

        if (version_compare($symfonyVersion, '3.4.0', 'gte')) {
            $this->expectException(CommandExecutionException::class);
            new Process('echo', null, null, null, 60);
            $process = new Process([], null, null, null, 60);
            $this->assertInstanceOf(Process::class, $process, 'Successfully created Process');
        }
    }
}
