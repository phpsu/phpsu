<?php

declare(strict_types=1);

namespace PHPSu\Tests\Config;

use PHPSu\Config\AppInstance;
use PHPSu\Config\Compression\Bzip2Compression;
use PHPSu\Config\Compression\GzipCompression;
use PHPUnit\Framework\TestCase;

final class AppInstanceTest extends TestCase
{
    public function testSetHostException()
    {
        $apps = new AppInstance();
        $this->expectExceptionMessage('host incorrect/Host has invalid character');
        $apps->setHost('incorrect/Host');
    }

    public function testCompressionSettings()
    {
        $app = new AppInstance();
        $this->assertEquals([], $app->getCompressions());

        $gzipCompression = new GzipCompression();
        $app->setCompressions($gzipCompression);
        $this->assertEquals([$gzipCompression], $app->getCompressions());

        $bzip2Compression = new Bzip2Compression();
        $app->setCompressions($gzipCompression, $bzip2Compression);
        $this->assertEquals([$gzipCompression, $bzip2Compression], $app->getCompressions());

        $app->setCompressions();
        $this->assertEquals([], $app->getCompressions());

        $app->setCompressions($bzip2Compression, $gzipCompression);
        $this->assertEquals([$bzip2Compression, $gzipCompression], $app->getCompressions());

        $app->unsetCompressions();
        $this->assertEquals([], $app->getCompressions());
    }
}
