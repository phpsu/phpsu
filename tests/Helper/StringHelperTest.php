<?php

declare(strict_types=1);

namespace PHPSu\Tests\Helper;

use PHPSu\Helper\StringHelper;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\OutputInterface;

class StringHelperTest extends TestCase
{
    public function testThatSplitStringSplitsEmptyString()
    {
        $result = StringHelper::splitString('', 10);
        $this->assertSame([''], $result);
    }
    public function testThatSplitStringSplitsAtRightPosition()
    {
        $result = StringHelper::splitString('test 12_1 12_2 12_3', 10);
        $this->assertSame(['test 12_1', '12_2 12_3'], $result);
    }

    public function testThatSplitStringCannotForceSplit()
    {
        $result = StringHelper::splitString('test_test_test_test', 10);
        $this->assertSame(['test_test_test_test'], $result);
    }

    public function testThatSplitStringCanSplitLarge()
    {
        $result = StringHelper::splitString('test test_test_test_test_test_test_test_test_test', 30);
        $this->assertSame(['test', 'test_test_test_test_test_test_test_test_test'], $result);
    }

    public function testThatSplitStringCanSplitOnEdgeCase()
    {
        $result = StringHelper::splitString('123 4 5678', 5);
        $this->assertSame(['123 4', '5678'], $result);
    }

    public function testFindStringInArray()
    {
        $this->assertSame('production', StringHelper::findStringInArray('production', ['production']), 'perfect match');
        $this->assertSame('production', StringHelper::findStringInArray('p', ['production']), 'only first letter');
        $this->assertSame('PrOduction', StringHelper::findStringInArray('p', ['PrOduction']), 'case insensitive');
        $this->assertSame('production', StringHelper::findStringInArray('p', ['production', 'local']), 'with another element in list');
        $this->assertSame('local', StringHelper::findStringInArray('l', ['production', 'local']), 'different letter');
        $this->assertSame('', StringHelper::findStringInArray('l', ['london', 'local']), 'if inconclusive');
        $this->assertSame('', StringHelper::findStringInArray('', ['london', 'local']), 'empty input');
    }

    public function testUndefinedVerbosityException()
    {
        $verbosity = 9999;
        $this->expectExceptionMessage("Verbosity $verbosity is not defined");
        StringHelper::optionStringForVerbosity($verbosity);
    }
}
