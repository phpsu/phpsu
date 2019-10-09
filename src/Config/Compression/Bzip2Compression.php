<?php

declare(strict_types=1);

namespace PHPSu\Config\Compression;

final class Bzip2Compression implements CompressionInterface
{
    public function getCompressCommand(): string
    {
        return ' | bzip2';
    }

    public function getUnCompressCommand(): string
    {
        return 'bunzip2 | ';
    }
}
