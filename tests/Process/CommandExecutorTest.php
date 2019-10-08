<?php

declare(strict_types=1);

namespace PHPSu\Tests\Process;

use PHPSu\Process\CommandExecutor;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\Output;

class CommandExecutorTest extends TestCase
{
    public function testPassthruPassesSuccessfullyThrough()
    {
        $mockOutput = $this->createMock(Output::class);
        $mockOutput->expects($this->once())->method('isDecorated')->willReturn(false);
        $mockOutput->expects($this->once())->method('write')->with("foo\n");
        $mockOutput->expects($this->never())->method('writeLn');
        $commandExecutor = new CommandExecutor();
        $exitCode = $commandExecutor->passthru('echo "foo"', $mockOutput);
        $this->assertSame(0, $exitCode);
    }

    public function testPassthruPassesUnsuccessfullyThrough()
    {
        $mockOutput = $this->createMock(ConsoleOutput::class);
        $mockErrorOutput = $this->createMock(ConsoleOutput::class);
        $mockOutput->expects($this->once())->method('isDecorated')->willReturn(false);
        $mockOutput->expects($this->once())->method('getErrorOutput')->willReturn($mockErrorOutput);
        $mockOutput->expects($this->never())->method('write');
        $mockOutput->expects($this->never())->method('writeLn');

        $mockErrorOutput->expects($this->once())->method('write')->with("sh: 1: ewj: not found\n");

        $commandExecutor = new CommandExecutor();
        $exitCode = $commandExecutor->passthru('ewj 2', $mockOutput);
        $this->assertSame(127, $exitCode, 'command shouldn\'t be installed');
    }

    public function testPassthruPassesTtyThrough()
    {
        $mockOutput = $this->createMock(Output::class);
        $mockOutput->expects($this->once())->method('isDecorated')->willReturn(true);
        $mockOutput->expects($this->never())->method('write');
        $mockOutput->expects($this->never())->method('writeLn');
        $commandExecutor = new CommandExecutor();
        $exitCode = $commandExecutor->passthru('ewj 2> /dev/null', $mockOutput);
        $this->assertNotEquals(0, $exitCode);
        $this->assertSame(127, $exitCode, 'command shouldn\'t be installed');
    }

    public function testExecuteCommandsParallel()
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
