<?php

declare(strict_types=1);

namespace PHPSu\Tests\Process;

use PHPSu\Process\Spinner;
use PHPUnit\Framework\TestCase;

class SpinnerTest extends TestCase
{
    public function testSpinForRandomState()
    {
        $number = \random_int(0, \count(Spinner::PONG) - 1);
        $spinner = $this->setSpinnerStateToNumber(new Spinner(), $number);
        $this->assertSame(Spinner::PONG[$number], $spinner->spin());
    }

    public function testSpinForLargeNumberToZeroAsState()
    {
        $number = 100000000000000;
        $spinner = $this->setSpinnerStateToNumber(new Spinner(), $number);
        $this->assertSame(Spinner::PONG[0], $spinner->spin());
    }

    private function setSpinnerStateToNumber(Spinner $spinner, int $number): Spinner
    {
        $reflection =  (new \ReflectionClass($spinner))->getProperty('state');
        $reflection->setAccessible(true);
        $reflection->setValue($spinner, $number);
        return $spinner;
    }
}
