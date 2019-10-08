<?php

declare(strict_types=1);

namespace PHPSu\Tests\Process;

use PHPSu\Process\OutputCallback;
use PHPSu\Process\Process;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;

final class OutputCallbackTest extends TestCase
{
    public function testProcessColorStd()
    {
        $output = new BufferedOutput(OutputInterface::VERBOSITY_NORMAL, true);
        $callback = new OutputCallback($output);
        $callback->__invoke(Process::fromShellCommandline('sleep 1')->setName('testName'), Process::OUT, 'message');
        $this->assertSame('', $output->fetch());
    }

    public function testProcessColorStdVerbose()
    {
        $output = new BufferedOutput(OutputInterface::VERBOSITY_VERBOSE, true);
        $callback = new OutputCallback($output);
        $callback->__invoke(Process::fromShellCommandline('sleep 1')->setName('testName'), Process::OUT, 'message');
        $this->assertSame("\e[33mtestName:\e[39m message\n", $output->fetch());
    }

    public function testProcessColorError()
    {
        $output = new BufferedOutput(OutputInterface::VERBOSITY_NORMAL, true);
        $callback = new OutputCallback($output);
        $callback->__invoke(Process::fromShellCommandline('sleep 1')->setName('testName'), Process::ERR, 'message');
        $this->assertSame("\e[31mtestName:\e[39m message\n", $output->fetch());
    }

    public function testProcessColorErrorMultiline()
    {
        $output = new BufferedOutput(OutputInterface::VERBOSITY_NORMAL, true);
        $callback = new OutputCallback($output);
        $callback->__invoke(Process::fromShellCommandline('sleep 1')->setName('testName'), Process::ERR, 'message' . PHP_EOL . 'message2');
        $this->assertSame("\e[31mtestName:\e[39m message\n" . "\e[31mtestName:\e[39m message2\n", $output->fetch());
    }

    public function testProcessColorStdVerboseMultiline()
    {
        $output = new BufferedOutput(OutputInterface::VERBOSITY_VERBOSE, true);
        $callback = new OutputCallback($output);
        $callback->__invoke(Process::fromShellCommandline('sleep 1')->setName('testName'), Process::OUT, 'message' . PHP_EOL . 'message2');
        $this->assertSame("\e[33mtestName:\e[39m message\n" . "\e[33mtestName:\e[39m message2\n", $output->fetch());
    }

    public function testProcessColorErrQuite()
    {
        $output = new BufferedOutput(OutputInterface::VERBOSITY_QUIET, true);
        $callback = new OutputCallback($output);
        $callback->__invoke(Process::fromShellCommandline('sleep 1')->setName('testName'), Process::ERR, 'message');
        $this->assertSame("\e[31mtestName:\e[39m message\n", $output->fetch());
    }

    public function testProcessErr()
    {
        $output = new BufferedOutput(OutputInterface::VERBOSITY_NORMAL, false);
        $callback = new OutputCallback($output);
        $callback->__invoke(Process::fromShellCommandline('sleep 1')->setName('testName'), Process::ERR, 'message');
        $this->assertSame("testName: message\n", $output->fetch());
    }

    public function testProcessStdVerbose()
    {
        $output = new BufferedOutput(OutputInterface::VERBOSITY_VERBOSE, false);
        $callback = new OutputCallback($output);
        $callback->__invoke(Process::fromShellCommandline('sleep 1')->setName('testName'), Process::OUT, 'message');
        $this->assertSame("testName: message\n", $output->fetch());
    }

    public function testProcessMultipleCalls()
    {
        $output = new BufferedOutput(OutputInterface::VERBOSITY_VERBOSE, true);
        $callback = new OutputCallback($output);
        $callback->__invoke(Process::fromShellCommandline('sleep 1')->setName('testName'), Process::ERR, 'message');
        $callback->__invoke(Process::fromShellCommandline('sleep 2')->setName('testName2'), Process::OUT, 'message2');
        $this->assertSame("\e[31mtestName:\e[39m message\n" . "\e[33mtestName2:\e[39m message2\n", $output->fetch());
    }
}
