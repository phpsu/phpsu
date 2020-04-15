<?php

declare(strict_types=1);

namespace PHPSu\Tests\Config;

use PHPSu\Config\TempSshConfigFile;
use PHPUnit\Framework\TestCase;

final class TempSshConfigFileTest extends TestCase
{
    /** @var string */
    private $oldCwd;

    public function setUp(): void
    {
        $this->oldCwd = getcwd();
        chdir(__DIR__ . '/../fixtures');
        exec(sprintf('rm -rf %s', escapeshellarg(__DIR__ . '/../fixtures/.phpsu/')));
    }

    public function testConstruct(): void
    {
        $file = new TempSshConfigFile();
        $this->assertSame('', implode('', iterator_to_array($file)));
        $this->assertFileExists(__DIR__ . '/../fixtures/.phpsu/config/ssh_config');
    }

    public function tearDown(): void
    {
        exec(sprintf('rm -rf %s', escapeshellarg(__DIR__ . '/../fixtures/.phpsu/')));
        chdir($this->oldCwd);
    }
}
