<?php
declare(strict_types=1);

namespace PHPSu\Tests\Beta;

use PHPSu\Process\Process;
use PHPSu\Process\ProcessManager;
use PHPSu\Process\StateChangeCallback;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\ConsoleSectionOutput;
use Symfony\Component\Console\Output\OutputInterface;

class StateChangeCallbackTest extends TestCase
{
    public function testNormalReady()
    {
        $output = new BufferedOutput(OutputInterface::VERBOSITY_NORMAL, true);
        $manager = new ProcessManager();
        $callback = new StateChangeCallback($output);
        $callback->__invoke(0, (new Process(['sleep', '1']))->setName('sleepProcess'), Process::STATE_READY, $manager);
        $this->assertSame("\033[37msleepProcess:\033[39m  \n", $output->fetch());
    }

    public function testNormalRunning()
    {
        $output = new BufferedOutput(OutputInterface::VERBOSITY_NORMAL, true);
        $manager = new ProcessManager();
        $callback = new StateChangeCallback($output);
        $callback->__invoke(0, (new Process(['sleep', '1']))->setName('sleepProcess'), Process::STATE_RUNNING, $manager);
        $this->assertSame("\033[33msleepProcess:\033[39m (      )\n", $output->fetch());
    }

    public function testNormalSucceeded()
    {
        $output = new BufferedOutput(OutputInterface::VERBOSITY_NORMAL, true);
        $manager = new ProcessManager();
        $callback = new StateChangeCallback($output);
        $callback->__invoke(0, (new Process(['sleep', '1']))->setName('sleepProcess'), Process::STATE_SUCCEEDED, $manager);
        $this->assertSame("\033[32msleepProcess:\033[39m ✔\n", $output->fetch());
    }

    public function testNormalErrored()
    {
        $output = new BufferedOutput(OutputInterface::VERBOSITY_NORMAL, true);
        $manager = new ProcessManager();
        $callback = new StateChangeCallback($output);
        $callback->__invoke(0, (new Process(['sleep', '1']))->setName('sleepProcess'), Process::STATE_ERRORED, $manager);
        $this->assertSame("\033[31msleepProcess:\033[39m ✘\n", $output->fetch());
    }

    public function testSectionReady()
    {
        $sections = [];
        $outputStream = fopen('php://memory', 'rwb');
        $output = new ConsoleSectionOutput($outputStream, $sections, OutputInterface::VERBOSITY_NORMAL, true, new OutputFormatter());
        $manager = new ProcessManager();
        $callback = new StateChangeCallback($output);
        $callback->__invoke(0, (new Process(['sleep', '1']))->setName('sleepProcess'), Process::STATE_READY, $manager);
        rewind($outputStream);
        $this->assertSame("\n", stream_get_contents($outputStream));
    }

    public function testSectionReadyWithProcess()
    {
        $sections = [];
        $outputStream = fopen('php://memory', 'rwb');
        $output = new ConsoleSectionOutput($outputStream, $sections, OutputInterface::VERBOSITY_NORMAL, true, new OutputFormatter());
        $manager = new ProcessManager();
        $process = (new Process(['sleep', '1']))->setName('sleepProcess');
        $manager->addProcess($process);
        $callback = new StateChangeCallback($output);
        $callback->__invoke(0, $process, Process::STATE_READY, $manager);
        rewind($outputStream);
        $this->assertSame("\033[37msleepProcess:\033[39m  \n", stream_get_contents($outputStream));
    }

    public function testSectionRunningWithProcess()
    {
        $sections = [];
        $outputStream = fopen('php://memory', 'rwb');
        $output = new ConsoleSectionOutput($outputStream, $sections, OutputInterface::VERBOSITY_NORMAL, true, new OutputFormatter());
        $manager = new ProcessManager();
        $process = (new Process(['sleep', '1']))->setName('sleepProcess');
        $manager->addProcess($process);
        $callback = new StateChangeCallback($output);
        $callback->__invoke(0, $process, Process::STATE_READY, $manager);
        rewind($outputStream);
        $this->assertSame("\033[37msleepProcess:\033[39m  \n", stream_get_contents($outputStream));

        $this->setPrivateProperty($manager, 'processStates', [0 => Process::STATE_RUNNING]);

        $callback->__invoke(0, $process, Process::STATE_RUNNING, $manager);
        rewind($outputStream);
        $this->assertSame(
            "\033[37msleepProcess:\033[39m  \n\033[1A\033[0J\033[33msleepProcess:\033[39m (      )\n",
            stream_get_contents($outputStream)
        );
    }

    public function testSectionRunningWithProcessSpinner()
    {
        $sections = [];
        $outputStream = fopen('php://memory', 'rwb');
        $output = new ConsoleSectionOutput($outputStream, $sections, OutputInterface::VERBOSITY_NORMAL, true, new OutputFormatter());
        $manager = new ProcessManager();
        $process = (new Process(['sleep', '1']))->setName('sleepProcess');
        $manager->addProcess($process);
        $callback = new StateChangeCallback($output);
        $callback->__invoke(0, $process, Process::STATE_READY, $manager);
        rewind($outputStream);
        $this->assertSame("\033[37msleepProcess:\033[39m  \n", stream_get_contents($outputStream));
        $tickCallback = $callback->getTickCallback();
        $tickCallback($manager);

        $this->setPrivateProperty($manager, 'processStates', [0 => Process::STATE_RUNNING]);
        $callback->__invoke(0, $process, Process::STATE_RUNNING, $manager);
        rewind($outputStream);
        $this->assertSame(
            "\033[37msleepProcess:\033[39m  \n\033[1A\033[0J\033[33msleepProcess:\033[39m (      )\n",
            stream_get_contents($outputStream)
        );
        $callback->__invoke(0, $process, Process::STATE_RUNNING, $manager);
        rewind($outputStream);
        $this->assertSame(
            "\033[37msleepProcess:\033[39m  \n\033[1A\033[0J\033[33msleepProcess:\033[39m (      )\n\033[1A\033[0J\033[33msleepProcess:\033[39m (●     )\n",
            stream_get_contents($outputStream)
        );
        $callback->__invoke(0, $process, Process::STATE_RUNNING, $manager);
        rewind($outputStream);
        $this->assertSame(
            "\033[37msleepProcess:\033[39m  \n\033[1A\033[0J\033[33msleepProcess:\033[39m (      )\n\033[1A\033[0J\033[33msleepProcess:\033[39m (●     )\n\033[1A\033[0J\033[33msleepProcess:\033[39m ( ●    )\n",
            stream_get_contents($outputStream)
        );
    }

    public function setPrivateProperty($object, string $propertyName, $value): void
    {
        $property = (new \ReflectionClass($object))->getProperty($propertyName);
        $property->setAccessible(true);
        $property->setValue($object, $value);
    }
}
