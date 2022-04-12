<?php

declare(strict_types=1);

namespace PHPSu\Config\Compression;

/**
 * @api
 */
final class EmptyCompression implements CompressionInterface
{
    public function getCompressCommand(): string
    {
        return '';
    }

    public function getUnCompressCommand(): string
    {
        return '';
    }
}
