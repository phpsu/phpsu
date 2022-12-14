<?php

declare(strict_types=1);

namespace PHPSu\Tests\Process;

use LogicException;
use PHPSu\Process\Process;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class ProcessTest extends TestCase
{
    public function testImpossibleStateException(): void
    {
        $process = Process::fromShellCommandline('');
        $reflectionClass =  (new ReflectionClass($process))->getParentClass();
        assert($reflectionClass instanceof ReflectionClass);
        $reflection = $reflectionClass->getProperty('status');
        $reflection->setAccessible(true);
        $reflection->setValue($process, '-1');
        $this->assertSame('-1', $process->getStatus());
        $this->expectException(LogicException::class);
        $process->getState();
    }
}
