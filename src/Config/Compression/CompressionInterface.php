<?php

declare(strict_types=1);

namespace PHPSu\Config\Compression;

interface CompressionInterface
{
    public function getCompressCommand(): string;

    public function getUnCompressCommand(): string;
}
