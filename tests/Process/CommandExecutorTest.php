<?php
declare(strict_types=1);


namespace PHPSu\Tests\Process;

use PHPSu\Process\CommandExecutor;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\ConsoleOutput;

class CommandExecutorTest extends TestCase
{
    public function testPassthruPassesSuccessfullyThrough(): void
    {
        $this->markTestSkipped('this test outputs to the real console.');
        $commandExecutor = new CommandExecutor();
        $output = new ConsoleOutput();
        $exitCode = $commandExecutor->passthru('echo', $output);
        $this->assertEquals(0, $exitCode);
    }

    public function testPassthruPassesUnSuccessfullyThrough(): void
    {
        $this->markTestSkipped('this test outputs to the real console.');
        $commandExecutor = new CommandExecutor();
        $output = new ConsoleOutput();
        $exitCode = $commandExecutor->passthru('ewj', $output);
        $this->assertNotEquals(0, $exitCode);
        $this->assertEquals(127, $exitCode, 'command shouldn\'t be installed');
    }

    public function testExecuteCommandsParallel(): void
    {
        $this->markTestSkipped('this test has Timing issues');
        $commandExecutor = new CommandExecutor();
        $outputLog = new BufferedOutput();
        $outputStatus = new BufferedOutput();
        $commandExecutor->executeParallel(['A' => 'echo hi', 'B' => 'echo world'], $outputLog, $outputStatus);
        $fetch = $outputStatus->fetch();
        $this->assertContains('A: ✔', $fetch);
        $this->assertContains('B: ✔', $fetch);
    }
}
