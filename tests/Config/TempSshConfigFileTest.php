<?php

declare(strict_types=1);

namespace PHPSu\Tests\Config;

use ReflectionClass;
use Exception;
use PHPSu\Config\TempSshConfigFile;
use PHPSu\Tests\Command\CommandGeneratorTest;
use PHPUnit\Framework\TestCase;

final class TempSshConfigFileTest extends TestCase
{
    private string $oldCwd = '';

    protected function setUp(): void
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
        $reflection = new ReflectionClass(TempSshConfigFile::class);
        $oldValue = $reflection->getStaticPropertyValue('fileName');
        $reflection->setStaticPropertyValue('fileName', '/etc/hosts/.phpsu/ssh_config');

        static::expectException(Exception::class);
        static::expectExceptionMessage(sprintf('Directory "%s" was not created', '/etc/hosts/.phpsu'));
        try {
            new TempSshConfigFile();
        } finally {
            $reflection->setStaticPropertyValue('fileName', $oldValue);
        }
    }

    protected function tearDown(): void
    {
        exec(sprintf('rm -rf %s', escapeshellarg(__DIR__ . '/../fixtures/.phpsu/')));
        chdir($this->oldCwd);
    }
}
