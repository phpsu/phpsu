<?php
declare(strict_types=1);

namespace PHPSu\Tests\Process;

use PHPSu\Process\Process;
use PHPSu\Process\ProcessManager;
use PHPUnit\Framework\TestCase;

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
        $this->assertNotTrue($pList1->isRunning(), 'Process1 shouldn\'t be running');
        $this->assertNotTrue($pList2->isRunning(), 'Process2 shouldn\'t be running');
        $this->assertSame('Testing List1' . PHP_EOL, $pList1->getOutput());
        $this->assertSame('Testing List2' . PHP_EOL, $pList2->getOutput());
    }

    public function testRunWithError(): void
    {
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
        } catch (\Exception $exception) {
            $this->assertSame([$name => $pError->getErrorOutput()], $processManager->getErrorOutputs());
            return;
        }
        $this->assertTrue(false, 'Exception should be thrown');
    }
}
