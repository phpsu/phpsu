<?php

declare(strict_types=1);

namespace PHPSu\Tests\Config;

use PHPSu\Config\SshConfigDownloader;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class SshConfigDownloaderTest extends TestCase
{
    /** @var array<array{string, string[]}> */
    private array $requests = [];

    public function testDownload(): void
    {
        $downloader = $this->createDownloader([
            [['HTTP/1.1 200 OK', 'Content-Type: text/plain'], "Host a\n"],
        ]);
        $this->assertSame("Host a\n", $downloader->download('https://example.com/ssh_config'));
        $this->assertSame([['https://example.com/ssh_config', ['User-Agent: phpsu']]], $this->requests);
    }

    public function testDownloadWithBasicAuth(): void
    {
        $downloader = $this->createDownloader([
            [['HTTP/1.1 200 OK'], "Host a\n"],
        ]);
        $downloader->download('https://example.com/ssh_config', 'user', 'secret');
        $this->assertSame([['https://example.com/ssh_config', ['User-Agent: phpsu', 'Authorization: Basic ' . base64_encode('user:secret')]]], $this->requests);
    }

    public function testRedirectsKeepCredentialsOnlyForTheSameHost(): void
    {
        $downloader = $this->createDownloader([
            [['HTTP/1.1 301 Moved Permanently', 'Location: /moved/ssh_config'], ''],
            [['HTTP/1.1 302 Found', 'location: https://cdn.example.org/ssh_config'], ''],
            [['HTTP/1.1 200 OK'], "Host b\n"],
        ]);
        $this->assertSame("Host b\n", $downloader->download('https://example.com/ssh_config', 'user', 'secret'));
        $authorization = 'Authorization: Basic ' . base64_encode('user:secret');
        $this->assertSame([
            ['https://example.com/ssh_config', ['User-Agent: phpsu', $authorization]],
            ['https://example.com/moved/ssh_config', ['User-Agent: phpsu', $authorization]],
            ['https://cdn.example.org/ssh_config', ['User-Agent: phpsu']],
        ], $this->requests);
    }

    public function testRedirectToHttpIsRefused(): void
    {
        $downloader = $this->createDownloader([
            [['HTTP/1.1 302 Found', 'Location: http://example.com/ssh_config'], ''],
        ]);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('only https urls are allowed, got http://example.com/ssh_config');
        $downloader->download('https://example.com/ssh_config');
    }

    public function testTooManyRedirects(): void
    {
        $responses = array_fill(0, SshConfigDownloader::MAX_REDIRECTS + 1, [['HTTP/1.1 302 Found', 'Location: /loop'], '']);
        $responses[] = [['HTTP/1.1 200 OK'], 'never reached'];
        $downloader = $this->createDownloader($responses);
        try {
            $downloader->download('https://example.com/ssh_config');
            $this->fail('exception expected');
        } catch (RuntimeException $runtimeException) {
            $this->assertSame('download of https://example.com/loop failed: too many redirects', $runtimeException->getMessage());
        }

        $this->assertCount(SshConfigDownloader::MAX_REDIRECTS + 1, $this->requests);
    }

    public function testErrorStatus(): void
    {
        $downloader = $this->createDownloader([
            [['HTTP/1.1 404 Not Found'], 'not found'],
        ]);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('download of https://example.com/ssh_config failed with HTTP status 404');
        $downloader->download('https://example.com/ssh_config');
    }

    public function testRedirectWithoutLocationIsAnError(): void
    {
        $downloader = $this->createDownloader([
            [['HTTP/1.1 304 Not Modified'], ''],
        ]);
        $this->expectExceptionMessage('download of https://example.com/ssh_config failed with HTTP status 304');
        $downloader->download('https://example.com/ssh_config');
    }

    public function testParseResponseHeadersUsesTheLastResponse(): void
    {
        $downloader = new SshConfigDownloader();
        $this->assertSame([0, []], $downloader->parseResponseHeaders([]));
        $this->assertSame([200, ['content-type' => 'text/plain', 'x-time' => '12:30']], $downloader->parseResponseHeaders([
            'HTTP/1.1 100 Continue',
            'X-Old: old',
            'HTTP/2 200',
            ' Content-Type :  text/plain ',
            'X-Time: 12:30',
            'invalid header line',
        ]));
    }

    public function testResolveLocation(): void
    {
        $downloader = new SshConfigDownloader();
        $this->assertSame('https://other.example.com/a', $downloader->resolveLocation('https://example.com/dir/file', 'https://other.example.com/a'));
        $this->assertSame('https://cdn.example.com/a', $downloader->resolveLocation('https://example.com/dir/file', '//cdn.example.com/a'));
        $this->assertSame('https://example.com:8443/a', $downloader->resolveLocation('https://user@example.com:8443/dir/file', '/a'));
        $this->assertSame('https://example.com/dir/other', $downloader->resolveLocation('https://example.com/dir/file', 'other'));
        $this->assertSame('https://example.com/other', $downloader->resolveLocation('https://example.com', 'other'));
    }

    public function testStreamContextOptions(): void
    {
        $this->assertSame([
            'http' => [
                'method' => 'GET',
                'header' => ['User-Agent: phpsu'],
                'timeout' => 2.5,
                'follow_location' => 0,
                'ignore_errors' => true,
            ],
        ], (new SshConfigDownloader(2.5))->getStreamContextOptions(['User-Agent: phpsu']));
    }

    public function testStreamTransport(): void
    {
        $this->assertSame([[], "Host a\n"], (new SshConfigDownloader())->streamTransport('data://text/plain;base64,' . base64_encode("Host a\n"), []));
    }

    public function testOnlyHttpsIsAllowed(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('only https urls are allowed, got http://example.com/ssh_config');
        (new SshConfigDownloader())->download('http://example.com/ssh_config');
    }

    public function testConnectionErrorThrowsRuntimeException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('download of https://127.0.0.1:1/ssh_config failed: ');
        (new SshConfigDownloader(0.5))->download('https://127.0.0.1:1/ssh_config', 'user', 'password');
    }

    /**
     * @param array<array{string[], string}> $responses
     */
    private function createDownloader(array $responses): SshConfigDownloader
    {
        return new SshConfigDownloader(10.0, function (string $url, array $headers) use (&$responses): array {
            $this->requests[] = [$url, $headers];
            $response = array_shift($responses);
            assert(is_array($response));
            return $response;
        });
    }
}
