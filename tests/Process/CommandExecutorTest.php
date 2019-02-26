<?php
declare(strict_types=1);


namespace PHPSu\Tests\Process;

use PHPSu\Process\CommandExecutor;
use PHPSu\Tests\TestHelper\BufferedConsoleOutput;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\ConsoleOutput;

class CommandExecutorTest extends TestCase
{
    public function testPassthruPassesSuccessfullyThrough(): void
    {
        $commandExecutor = new CommandExecutor();
        $exitCode = $commandExecutor->passthru('echo "foo" >> /dev/null');
        $this->assertEquals(0, $exitCode);
    }

    public function testPassthruPassesUnsuccessfullyThrough(): void
    {
        $commandExecutor = new CommandExecutor();
        $exitCode = $commandExecutor->passthru('ewj 2> /dev/null');
        $this->assertNotEquals(0, $exitCode);
        $this->assertEquals(127, $exitCode, 'command shouldn\'t be installed');
    }

    public function testExecuteCommandsParallel(): void
    {
        $commandExecutor = new CommandExecutor();
        $outputLog = new BufferedOutput();
        $outputStatus = new BufferedOutput();
        $commandExecutor->executeParallel(['A' => 'echo hi', 'B' => 'echo world'], $outputLog, $outputStatus);
        $fetch = $outputStatus->fetch();
        $this->assertContains('A: ✔', $fetch);
        $this->assertContains('B: ✔', $fetch);
    }
}
