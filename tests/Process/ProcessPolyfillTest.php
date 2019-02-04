<?php
declare(strict_types=1);

namespace PHPSu\Tests\Process;

use PHPSu\Process\CommandExecutor;
use PHPSu\Process\Process;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;

class ProcessPolyfillTest extends TestCase
{
    public function testProcessCommandToString(): void
    {
        $method = $this->getReflectedProcessMethod('commandToString');
        $this->assertEquals(
            'exec \'echo\' \'hi\'',
            $method->invokeArgs(new Process('echo hi'), [['echo', 'hi']]),
            'converting a command array successfully to string'
        );
    }

    private function getReflectedProcessMethod($method): ReflectionMethod
    {
        $reflectedProcess = new ReflectionClass(Process::class);
        $method = $reflectedProcess->getMethod($method);
        $method->setAccessible(true);
        return $method;
    }

    public function testProcessCommandOutput(): void
    {
        $method = $this->getReflectedProcessMethod('commandToString');
        $result = (new CommandExecutor())->executeDirectly($method->invokeArgs(new Process(''), [['echo', 'hi']]));
        $this->assertIsArray($result, 'command executor returned array successfully');
        if (\is_array($result)) {
            $this->assertEquals($result[0], Process::OUT, 'Executor output type was correct: ' . Process::OUT);
            $this->assertEquals($result[1], 'hi' . PHP_EOL, 'Executor output was correct: hi');
        }
    }
}
