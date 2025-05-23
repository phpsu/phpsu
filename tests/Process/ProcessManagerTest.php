<?php

declare(strict_types=1);

namespace PHPSu\Tests\Process;

use Closure;
use Exception;
use PHPSu\Process\Process;
use PHPSu\Process\ProcessManager;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class ProcessManagerTest extends TestCase
{
    public function testProcessesShouldBeRunning(): void
    {
        $processManager = new ProcessManager();
        $processManager->addProcess($pList1 = Process::fromShellCommandline('echo "Testing List1" && sleep 0.1')->setName('list1'));
        $processManager->addProcess($pList2 = Process::fromShellCommandline('echo "Testing List2" && sleep 0.1')->setName('list2'));
        $processManager->start();
        $this->assertTrue($pList1->isRunning(), 'Process1 should be running');
        $this->assertTrue($pList2->isRunning(), 'Process2 should be running');
        $processManager->wait();
        $this->assertNotTrue($pList1->isRunning(), "Process1 shouldn't be running");
        $this->assertNotTrue($pList2->isRunning(), "Process2 shouldn't be running");
        $this->assertSame('Testing List1' . PHP_EOL, $pList1->getOutput());
        $this->assertSame('Testing List2' . PHP_EOL, $pList2->getOutput());
    }

    public function testProcessGetState(): void
    {
        $processManager = new ProcessManager();
        $pList1 = Process::fromShellCommandline('echo "Testing List1" && sleep 0.1')->setName('list1');
        $processManager->addProcess($pList1);
        $processManager->start();

        $pid = $pList1->getPid();
        // forcefully kill process
        $pList1->stop(0, 15);
        static::expectExceptionMessage('No Process found with id: ' . $pid);
        assert($pid !== null);
        $processManager->getState($pid);
    }

    public function testRunWithError(): void
    {
        $this->expectException(Exception::class);
        $name = 'error' . md5(random_bytes(100));
        $processManager = (new ProcessManager())
            ->addProcess(Process::fromShellCommandline('error')->setName($name))
            ->start()
            ->wait();
        $this->expectExceptionMessage('Error in Process ' . $name);
        $processManager->validateProcesses();
    }

    public function testRunWithMultipleErrors(): void
    {
        $name1 = 'error' . md5(random_bytes(100));
        $name2 = 'error' . md5(random_bytes(100));
        $processManager = (new ProcessManager())
            ->addProcess(Process::fromShellCommandline('error')->setName($name1))
            ->addProcess(Process::fromShellCommandline('error2')->setName($name2))
            ->start()
            ->wait();
        $this->expectExceptionMessage('Error in Processes ' . $name1 . ', ' . $name2);
        $processManager->validateProcesses();
    }

    public function testRunGetErrorOutput(): void
    {
        $processManager = new ProcessManager();
        $name = 'error' . md5(random_bytes(100));
        $processManager->addProcess($pError = Process::fromShellCommandline('error')->setName($name));
        $processManager->addProcess($pList1 = Process::fromShellCommandline('echo "Testing List1"')->setName('list1'));
        $processManager->start();
        try {
            $processManager->wait()->validateProcesses();
        } catch (Exception) {
            $this->assertSame([$name => $pError->getErrorOutput()], $processManager->getErrorOutputs());
            return;
        }

        throw new Exception('Expected exception not thrown');
    }

    public function testAddOutputCallback(): void
    {
        $processManager = new ProcessManager();
        $processManager->addOutputCallback(static fn(): bool => true);

        $property = (new ReflectionClass($processManager))->getProperty('outputCallbacks');
        $property->setAccessible(true);

        $callbacks = $property->getValue($processManager);
        assert(is_array($callbacks));
        foreach ($callbacks as $callback) {
            assert($callback instanceof Closure);
            $this->assertTrue($callback());
        }
    }

    public function testAddStateChangeCallback(): void
    {
        $processManager = new ProcessManager();
        $processManager->addStateChangeCallback(static fn(): bool => true);

        $property = (new ReflectionClass($processManager))->getProperty('stateChangeCallbacks');
        $property->setAccessible(true);

        $callbacks = $property->getValue($processManager);
        assert(is_array($callbacks));
        foreach ($callbacks as $callback) {
            assert($callback instanceof Closure);
            $this->assertTrue($callback());
        }
    }

    public function testAddTickCallback(): void
    {
        $processManager = new ProcessManager();
        $processManager->addTickCallback(static fn(): bool => true);

        $property = (new ReflectionClass($processManager))->getProperty('tickCallbacks');
        $property->setAccessible(true);

        $callbacks = $property->getValue($processManager);
        assert(is_array($callbacks));
        foreach ($callbacks as $callback) {
            assert($callback instanceof Closure);
            $this->assertTrue($callback());
        }
    }

    public function testProcessManagerMustRun(): void
    {
        $result = (new ProcessManager())
            ->addProcess($pError = Process::fromShellCommandline('echo hi'))
            ->mustRun();
        $this->assertContains($result->getState(0), [Process::STATE_RUNNING, Process::STATE_SUCCEEDED]);
    }

    public function testEchoToStderrDoseNotMeanTheProcessErrored(): void
    {
        $processManager = (new ProcessManager())
            ->addProcess($pError = Process::fromShellCommandline('>&2 echo hi '))
            ->mustRun();
        $this->assertContains($processManager->getState(0), [Process::STATE_SUCCEEDED]);
    }
}
