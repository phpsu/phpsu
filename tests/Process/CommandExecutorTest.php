<?php

declare(strict_types=1);

namespace PHPSu\Tests\Process;

use Generator;
use PHPSu\Process\CommandExecutor;
use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\Constraint\IsIdentical;
use PHPUnit\Framework\Constraint\StringContains;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\Output;

class CommandExecutorTest extends TestCase
{
    public static function provideCommands(): Generator
    {
        yield 'echo "hello world"' => [
            'echo "hello world"',
            0,
            new IsIdentical("hello world\n"),
            new IsIdentical('')
        ];
        yield 'ewj: not found' => [
            'ewj 2',
            127,
            new IsIdentical(''),
            new StringContains("ewj: not found"),
        ];
        yield 'no stderr output' => [
            'ewj 2> /dev/null',
            127,
            new IsIdentical(''),
            new IsIdentical(''),
        ];
    }

    /**
     * @dataProvider provideCommands
     */
    public function testPassthru(string $command, int $expectedExitCode, Constraint $expectedStdout, Constraint $expectedStderr): void
    {
        $stdoutStream = fopen('php://temp', 'rwb+');
        $this->assertIsResource($stdoutStream);
        $stderrStream = fopen('php://temp', 'rwb+');
        $this->assertIsResource($stderrStream);

        $commandExecutor = new CommandExecutor();
        $realExitCode = $commandExecutor->passthru($command, stdout: $stdoutStream, stderr: $stderrStream);
        rewind($stderrStream);
        static::assertThat(stream_get_contents($stderrStream), $expectedStderr, 'stderr');
        rewind($stdoutStream);
        static::assertThat(stream_get_contents($stdoutStream), $expectedStdout, 'stdout');
        $this->assertSame($expectedExitCode, $realExitCode, 'exit code should be 0');
    }

    public function testExecuteCommandsParallel(): void
    {
        $commandExecutor = new CommandExecutor();
        $outputLog = new BufferedOutput();
        $outputStatus = new BufferedOutput();
        $commandExecutor->executeParallel(['A' => 'echo hi', 'B' => 'echo world'], $outputLog, $outputStatus);
        $fetch = $outputStatus->fetch();
        $this->assertStringContainsString('A: ✔', $fetch);
        $this->assertStringContainsString('B: ✔', $fetch);
    }
}
