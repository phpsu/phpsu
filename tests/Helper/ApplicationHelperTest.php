<?php

declare(strict_types=1);

namespace PHPSu\Tests\Helper;

use ErrorException;
use PHPSu\Exceptions\EnvironmentException;
use PHPSu\Helper\ApplicationHelper;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class ApplicationHelperTest extends TestCase
{
    public const GIT_PATH = __DIR__ . '/../fixtures/git';

    public function testGetPhpsuVersionFromVendor(): void
    {
        $result = $this->callPrivateMethod('getPhpSuVersionFromVendor');
        $this->assertEmpty($result, 'Asserting phpsu-vendor version to be empty due to test context');
    }

    public function testGetPhpSuVersionFromGitFolder(): void
    {
        $versionString = $this->callPrivateMethod('getPhpSuVersionFromGitFolder', self::GIT_PATH);
        $this->assertIsString($versionString);
        assert(is_string($versionString)); # dumb phpstan :(
        $this->assertNotEmpty($versionString);
        $this->assertStringNotContainsString('ref: ', $versionString);
        $this->assertEquals('main', $versionString);
    }

    public function testGetPhpSuVersionFromGitFolderWithoutFolder(): void
    {
        $this->assertNull($this->callPrivateMethod('getPhpSuVersionFromGitFolder', self::GIT_PATH . '_'));
    }

    public function testGetPhpSuVersionFromGitFolderWithoutHeadFile(): void
    {
        $this->expectException(ErrorException::class);
        $this->callPrivateMethod('getPhpSuVersionFromGitFolder', self::GIT_PATH . '/no_head');
    }

    /**
     * @param string $method
     * @return mixed
     * @throws \ReflectionException
     */
    private function callPrivateMethod(string $method)
    {
        $object = new ApplicationHelper();
        $reflection = (new ReflectionClass($object))->getMethod($method);
        $reflection->setAccessible(true);
        $args = func_get_args();
        array_shift($args);
        return $reflection->invoke($object, ...$args);
    }
}
