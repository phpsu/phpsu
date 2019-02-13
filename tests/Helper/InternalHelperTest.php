<?php

namespace PHPSu\Tests\Helper;

use PHPSu\Exceptions\CommandExecutionException;
use PHPSu\Exceptions\EnvironmentException;
use PHPSu\Helper\InternalHelper;
use PHPSu\Tools\EnvironmentUtility;
use PHPUnit\Framework\TestCase;

class InternalHelperTest extends TestCase
{

    private const GIT_PATH = __DIR__ . '/../../.git';

    public function testGetPhpsuVersionFromVendor(): void
    {
        $result = $this->callPrivateMethod('getPhpSuVersionFromVendor');
        // PHPSU_VENDOR_INSTALLATION === false
        $this->assertEmpty($result, 'Asserting phpsu-vendor version to be empty due to test context');
    }

    public function testGetPhpSuVersionFromGitFolder(): void
    {
        $this->assertFileExists(self::GIT_PATH . '/HEAD');
        if (file_exists(self::GIT_PATH . '/HEAD')) {
            $this->assertNotEmpty($this->callPrivateMethod('getPhpSuVersionFromGitFolder'));
        } else {
            $this->expectException(EnvironmentException::class);
            $this->callPrivateMethod('getPhpSuVersionFromGitFolder');
        }
    }

    public function testGetPhpSuVersionFromGitCommand(): void
    {
        if (!(new EnvironmentUtility())->isGitInstalled()) {
            $this->expectException(CommandExecutionException::class);
            $this->callPrivateMethod('getPhpSuVersionFromGitCommand');
        } else {
            $result = $this->callPrivateMethod('getPhpSuVersionFromGitCommand');
            $this->assertNotEmpty($result, 'branch name could be everything but never empty');
        }
    }

    public function testIsGitFolderAvailable(): void
    {
        $this->assertSame(file_exists(self::GIT_PATH), $this->callPrivateMethod('isGitFolderAvailable'));
    }

    private function callPrivateMethod(string $method)
    {
        $object = new InternalHelper();
        $reflection =  (new \ReflectionClass($object))->getMethod($method);
        $reflection->setAccessible(true);
        return $reflection->invoke($object);
    }
}
