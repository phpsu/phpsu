<?php

declare(strict_types=1);

namespace PHPSu\Tests\Helper;

use ErrorException;
use PHPSu\Exceptions\EnvironmentException;
use PHPSu\Helper\ApplicationHelper;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class InternalHelperTest extends TestCase
{
    public const GIT_PATH = __DIR__ . '/../../.git';

    public function testGetPhpsuVersionFromVendor(): void
    {
        $result = $this->callPrivateMethod('getPhpSuVersionFromVendor');
        $this->assertEmpty($result, 'Asserting phpsu-vendor version to be empty due to test context');
    }

    public function testGetPhpSuVersionFromGitFolder(): void
    {
        $this->assertFileExists(self::GIT_PATH . '/HEAD');
        if (file_exists(self::GIT_PATH . '/HEAD')) {
            $versionString = $this->callPrivateMethod('getPhpSuVersionFromGitFolder');
            $this->assertIsString($versionString);
            $this->assertNotEmpty($versionString);
            $this->assertStringNotContainsString('ref: ', $versionString);
        } else {
            $this->expectException(EnvironmentException::class);
            $this->callPrivateMethod('getPhpSuVersionFromGitFolder');
        }
    }

    public function testGetPhpSuVersionFromGitFolderWithoutFolder(): void
    {
        rename(self::GIT_PATH, self::GIT_PATH . '_');
        try {
            $this->assertNull($this->callPrivateMethod('getPhpSuVersionFromGitFolder'));
        } finally {
            rename(self::GIT_PATH . '_', self::GIT_PATH);
        }
    }

    public function testGetPhpSuVersionFromGitFolderWithoutHeadFile(): void
    {
        rename(self::GIT_PATH . '/HEAD', self::GIT_PATH . '/HEAD_');
        try {
            $this->expectException(ErrorException::class);
            $this->callPrivateMethod('getPhpSuVersionFromGitFolder');
        } finally {
            rename(self::GIT_PATH . '/HEAD_', self::GIT_PATH . '/HEAD');
        }
    }

    private function callPrivateMethod(string $method)
    {
        $object = new ApplicationHelper();
        $reflection = (new ReflectionClass($object))->getMethod($method);
        $reflection->setAccessible(true);
        return $reflection->invoke($object);
    }
}
