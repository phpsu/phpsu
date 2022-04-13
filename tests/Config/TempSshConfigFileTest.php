<?php

declare(strict_types=1);

namespace PHPSu\Tests\Config;

use PHPSu\Config\TempSshConfigFile;
use PHPSu\Tests\Command\CommandGeneratorTest;
use PHPUnit\Framework\TestCase;

final class TempSshConfigFileTest extends TestCase
{
    private string $oldCwd = '';

    public function setUp(): void
    {
        $cwd = getcwd();
        assert(is_string($cwd));
        $this->oldCwd = $cwd;
        chdir(__DIR__ . '/../fixtures');
        exec(sprintf('rm -rf %s', escapeshellarg(__DIR__ . '/../fixtures/.phpsu/')));
    }

    public function testConstruct(): void
    {
        $file = new TempSshConfigFile();
        $this->assertSame('', CommandGeneratorTest::implodeTempFile($file));
        $this->assertFileExists(__DIR__ . '/../fixtures/.phpsu/config/ssh_config');
    }

    public function testConstructDifferentFolder(): void
    {
        $reflection = new \ReflectionClass(TempSshConfigFile::class);
        $property = $reflection->getProperty('fileName');
        $property->setAccessible(true);
        $oldValue = $property->getValue();
        $property->setValue('/etc/hosts/.phpsu/ssh_config');
        static::expectException(\Exception::class);
        static::expectExceptionMessage(sprintf('Directory "%s" was not created', '/etc/hosts/.phpsu'));
        try {
            new TempSshConfigFile();
        } finally {
            $property->setValue($oldValue);
        }
    }

    public function tearDown(): void
    {
        exec(sprintf('rm -rf %s', escapeshellarg(__DIR__ . '/../fixtures/.phpsu/')));
        chdir($this->oldCwd);
    }
}
