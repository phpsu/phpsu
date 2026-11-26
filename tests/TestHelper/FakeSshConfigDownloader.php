<?php

declare(strict_types=1);

namespace PHPSu\Tests\TestHelper;

use PHPSu\Config\SshConfigDownloaderInterface;
use RuntimeException;

final class FakeSshConfigDownloader implements SshConfigDownloaderInterface
{
    /** @var array<array{string, ?string, ?string}> */
    public array $calls = [];

    /**
     * @param array<string|RuntimeException> $responses
     */
    public function __construct(private array $responses)
    {
    }

    public function download(string $url, ?string $username = null, ?string $password = null): string
    {
        $this->calls[] = [$url, $username, $password];
        $response = array_shift($this->responses);
        if ($response instanceof RuntimeException) {
            throw $response;
        }

        return (string)$response;
    }
}
