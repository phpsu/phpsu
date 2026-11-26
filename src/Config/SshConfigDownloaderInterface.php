<?php

declare(strict_types=1);

namespace PHPSu\Config;

use RuntimeException;

/**
 * @internal
 */
interface SshConfigDownloaderInterface
{
    /**
     * @throws RuntimeException if the download failed
     */
    public function download(string $url, ?string $username = null, ?string $password = null): string;
}
