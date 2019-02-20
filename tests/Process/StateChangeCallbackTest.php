<?php
declare(strict_types=1);

namespace PHPSu\Tests\Process;

use PHPSu\Process\Process;
use PHPSu\Process\ProcessManager;
use PHPSu\Process\StateChangeCallback;
use PHPSu\Tools\EnvironmentUtility;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;

if (!class_exists('ConsoleSectionOutput', false)) {
    if (version_compare((new EnvironmentUtility())->getSymfonyProcessVersion(), '4.0.0', '>=')) {
        \class_alias(\Symfony\Component\Console\Output\ConsoleSectionOutput::class, 'ConsoleSectionOutput');
    } else {
        \class_alias(\PHPSu\Tools\ConsolePolyfill\ConsoleSectionOutput::class, 'ConsoleSectionOutput');
    }
}

use \ConsoleSectionOutput;

final class StateChangeCallbackTest extends TestCase
{
    public function testNormalReady(): void
    {
        $output = new BufferedOutput(OutputInterface::VERBOSITY_NORMAL, true);
        $manager = new ProcessManager();
        $callback = new StateChangeCallback($output);
        $callback->__invoke(0, Process::fromShellCommandline('sleep 1')->setName('sleepProcess'), Process::STATE_READY, $manager);
        $this->assertSame("\e[37msleepProcess:\e[39m  \n", $output->fetch());
    }

    public function testNormalRunning(): void
    {
        $output = new BufferedOutput(OutputInterface::VERBOSITY_NORMAL, true);
        $manager = new ProcessManager();
        $callback = new StateChangeCallback($output);
        $callback->__invoke(0, Process::fromShellCommandline('sleep 1')->setName('sleepProcess'), Process::STATE_RUNNING, $manager);
        $this->assertSame("\e[33msleepProcess:\e[39m (      )\n", $output->fetch());
    }

    public function testNormalSucceeded(): void
    {
        $output = new BufferedOutput(OutputInterface::VERBOSITY_NORMAL, true);
        $manager = new ProcessManager();
        $callback = new StateChangeCallback($output);
        $callback->__invoke(0, Process::fromShellCommandline('sleep 1')->setName('sleepProcess'), Process::STATE_SUCCEEDED, $manager);
        $this->assertSame("\e[32msleepProcess:\e[39m ✔\n", $output->fetch());
    }

    public function testNormalErrored(): void
    {
        $output = new BufferedOutput(OutputInterface::VERBOSITY_NORMAL, true);
        $manager = new ProcessManager();
        $callback = new StateChangeCallback($output);
        $callback->__invoke(0, Process::fromShellCommandline('sleep 1')->setName('sleepProcess'), Process::STATE_ERRORED, $manager);
        $this->assertSame("\e[31msleepProcess:\e[39m ✘\n", $output->fetch());
    }

    public function testSectionReady(): void
    {
        $sections = [];
        $outputStream = fopen('php://memory', 'rwb');
        $output = new ConsoleSectionOutput($outputStream, $sections, OutputInterface::VERBOSITY_NORMAL, true, new OutputFormatter());
        $manager = new ProcessManager();
        $callback = new StateChangeCallback($output);
        $callback->__invoke(0, Process::fromShellCommandline('sleep 1')->setName('sleepProcess'), Process::STATE_READY, $manager);
        rewind($outputStream);
        $this->assertSame("\n", stream_get_contents($outputStream));
    }

    public function testSectionReadyWithProcess(): void
    {
        $sections = [];
        $outputStream = fopen('php://memory', 'rwb');
        $output = new ConsoleSectionOutput($outputStream, $sections, OutputInterface::VERBOSITY_NORMAL, true, new OutputFormatter());
        $manager = new ProcessManager();
        $process = Process::fromShellCommandline('sleep 1')->setName('sleepProcess');
        $manager->addProcess($process);
        $callback = new StateChangeCallback($output);
        $callback->__invoke(0, $process, Process::STATE_READY, $manager);
        rewind($outputStream);
        $this->assertSame("\e[37msleepProcess:\e[39m  \n", stream_get_contents($outputStream));
    }

    public function testSectionRunningWithProcess(): void
    {
        $sections = [];
        $outputStream = fopen('php://memory', 'rwb');
        $output = new ConsoleSectionOutput($outputStream, $sections, OutputInterface::VERBOSITY_NORMAL, true, new OutputFormatter());
        $manager = new ProcessManager();
        $process = Process::fromShellCommandline('sleep 1')->setName('sleepProcess');
        $manager->addProcess($process);
        $callback = new StateChangeCallback($output);
        $callback->__invoke(0, $process, Process::STATE_READY, $manager);
        rewind($outputStream);
        $this->assertSame("\e[37msleepProcess:\e[39m  \n", stream_get_contents($outputStream));

        $this->setPrivateProperty($manager, 'processStates', [0 => Process::STATE_RUNNING]);

        $callback->__invoke(0, $process, Process::STATE_RUNNING, $manager);
        rewind($outputStream);
        $this->assertSame(
            "\e[37msleepProcess:\e[39m  \n\e[1A\e[0J\e[33msleepProcess:\e[39m (      )\n",
            stream_get_contents($outputStream)
        );
    }

    public function testSectionRunningWithProcessSpinner(): void
    {
        $sections = [];
        $outputStream = fopen('php://memory', 'rwb');
        $output = new ConsoleSectionOutput($outputStream, $sections, OutputInterface::VERBOSITY_NORMAL, true, new OutputFormatter());
        $manager = new ProcessManager();
        $process = Process::fromShellCommandline('sleep 1')->setName('sleepProcess');
        $manager->addProcess($process);
        $callback = new StateChangeCallback($output);
        $callback->__invoke(0, $process, Process::STATE_READY, $manager);
        rewind($outputStream);
        $this->assertSame("\e[37msleepProcess:\e[39m  \n", stream_get_contents($outputStream));
        $tickCallback = $callback->getTickCallback();
        $tickCallback($manager);

        $this->setPrivateProperty($manager, 'processStates', [0 => Process::STATE_RUNNING]);
        $callback->__invoke(0, $process, Process::STATE_RUNNING, $manager);
        rewind($outputStream);
        $this->assertSame(
            "\e[37msleepProcess:\e[39m  \n\e[1A\e[0J\e[33msleepProcess:\e[39m (      )\n",
            stream_get_contents($outputStream)
        );
        $callback->__invoke(0, $process, Process::STATE_RUNNING, $manager);
        rewind($outputStream);
        $this->assertSame(
            "\e[37msleepProcess:\e[39m  \n\e[1A\e[0J\e[33msleepProcess:\e[39m (      )\n\e[1A\e[0J\e[33msleepProcess:\e[39m (●     )\n",
            stream_get_contents($outputStream)
        );
        $callback->__invoke(0, $process, Process::STATE_RUNNING, $manager);
        rewind($outputStream);
        $this->assertSame(
            "\e[37msleepProcess:\e[39m  \n\e[1A\e[0J\e[33msleepProcess:\e[39m (      )\n\e[1A\e[0J\e[33msleepProcess:\e[39m (●     )\n\e[1A\e[0J\e[33msleepProcess:\e[39m ( ●    )\n",
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
