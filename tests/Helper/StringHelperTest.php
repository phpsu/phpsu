<?php
declare(strict_types=1);

namespace PHPSu\Tests\Helper;

use PHPSu\Helper\StringHelper;
use PHPUnit\Framework\TestCase;

class StringHelperTest extends TestCase
{
    public function testThatSplitStringSplitsAtRightPosition(): void
    {
        $result = StringHelper::splitString('test 12_1 12_2 12_3', 10);
        $this->assertSame(['test 12_1', '12_2 12_3'], $result);
    }

    public function testThatSplitStringCannotForceSplit(): void
    {
        $result = StringHelper::splitString('test_test_test_test', 10);
        $this->assertSame(['test_test_test_test'], $result);
    }

    public function testThatSplitStringCanSplitLarge(): void
    {
        $result = StringHelper::splitString('test test_test_test_test_test_test_test_test_test', 30);
        $this->assertSame(['test', 'test_test_test_test_test_test_test_test_test'], $result);
    }
}
