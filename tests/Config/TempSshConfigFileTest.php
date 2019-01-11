<?php
declare(strict_types=1);

namespace PHPSu\Tests\Config;

use PHPSu\Config\TempSshConfigFile;
use PHPUnit\Framework\TestCase;

class TempSshConfigFileTest extends TestCase
{
    /** @var string */
    private $oldCwd;

    public function setUp()
    {
        $this->oldCwd = getcwd();
        chdir(__DIR__ . '/../fixtures');
        $dir = __DIR__ . '/../fixtures/.phpsu/';
        exec(sprintf('rm -rf %s', escapeshellarg($dir)));
    }

    public function testConstruct(): void
    {
        $file = new TempSshConfigFile();
        $this->assertSame('', implode('', iterator_to_array($file)));
        $this->assertFileExists(__DIR__ . '/../fixtures/.phpsu/config/ssh_config');
    }

    public function tearDown()
    {
        chdir($this->oldCwd);
    }
}
