<?php

declare(strict_types=1);

namespace PHPSu\Config;

use Closure;
use RuntimeException;

/**
 * @internal
 */
final readonly class SshConfigDownloader implements SshConfigDownloaderInterface
{
    public const MAX_REDIRECTS = 5;

    /** @var Closure(string, string[]): array{string[], string} */
    private Closure $transport;

    /**
     * @param null|Closure(string $url, string[] $headers): array{string[], string} $transport returns the response header lines and the body
     */
    public function __construct(private float $timeout = 10.0, ?Closure $transport = null)
    {
        $this->transport = $transport ?? $this->streamTransport(...);
    }

    public function download(string $url, ?string $username = null, ?string $password = null): string
    {
        $originalHost = parse_url($url, PHP_URL_HOST);
        for ($redirects = 0; $redirects <= self::MAX_REDIRECTS; $redirects++) {
            if (stripos($url, 'https://') !== 0) {
                throw new RuntimeException(sprintf('only https urls are allowed, got %s', $url));
            }

            if (parse_url($url, PHP_URL_HOST) !== $originalHost) {
                // never send credentials to a different host
                $username = null;
                $password = null;
            }

            [$headerLines, $body] = ($this->transport)($url, $this->getRequestHeaders($username, $password));
            [$status, $headers] = $this->parseResponseHeaders($headerLines);
            if ($status >= 300 && $status < 400 && isset($headers['location'])) {
                $url = $this->resolveLocation($url, $headers['location']);
                continue;
            }

            if ($status !== 200) {
                throw new RuntimeException(sprintf('download of %s failed with HTTP status %d', $url, $status));
            }

            return $body;
        }

        throw new RuntimeException(sprintf('download of %s failed: too many redirects', $url));
    }

    /**
     * @return string[]
     */
    public function getRequestHeaders(?string $username, ?string $password): array
    {
        $headers = ['User-Agent: phpsu'];
        if ($username !== null) {
            $headers[] = 'Authorization: Basic ' . base64_encode($username . ':' . $password);
        }

        return $headers;
    }

    /**
     * @param string[] $headers
     * @return array{http: array<string, mixed>}
     */
    public function getStreamContextOptions(array $headers): array
    {
        return [
            'http' => [
                'method' => 'GET',
                'header' => $headers,
                'timeout' => $this->timeout,
                'follow_location' => 0,
                'ignore_errors' => true,
            ],
        ];
    }

    /**
     * @param string[] $headerLines
     * @return array{int, array<string, string>} status code and the headers of the last response
     */
    public function parseResponseHeaders(array $headerLines): array
    {
        $status = 0;
        $headers = [];
        foreach ($headerLines as $headerLine) {
            if (preg_match('#^HTTP/\S+\s+(\d{3})#', $headerLine, $matches)) {
                $status = (int)$matches[1];
                $headers = [];
                continue;
            }

            $parts = explode(':', $headerLine, 2);
            if (count($parts) === 2) {
                $headers[strtolower(trim($parts[0]))] = trim($parts[1]);
            }
        }

        return [$status, $headers];
    }

    public function resolveLocation(string $url, string $location): string
    {
        if (preg_match('#^[a-z][a-z0-9+.-]*://#i', $location)) {
            return $location;
        }

        if (str_starts_with($location, '//')) {
            return 'https:' . $location;
        }

        $parts = parse_url($url);
        $base = 'https://' . ($parts['host'] ?? '') . (isset($parts['port']) ? ':' . $parts['port'] : '');
        if (str_starts_with($location, '/')) {
            return $base . $location;
        }

        $path = $parts['path'] ?? '/';
        return $base . substr($path, 0, (int)strrpos($path, '/') + 1) . $location;
    }

    /**
     * @param string[] $headers
     * @return array{string[], string}
     */
    public function streamTransport(string $url, array $headers): array
    {
        $stream = @fopen($url, 'rb', false, stream_context_create($this->getStreamContextOptions($headers)));
        if ($stream === false) {
            throw new RuntimeException(sprintf('download of %s failed: %s', $url, error_get_last()['message'] ?? 'unknown error'));
        }

        $body = (string)stream_get_contents($stream);
        $wrapperData = stream_get_meta_data($stream)['wrapper_data'] ?? [];
        fclose($stream);

        return [array_values(array_filter(is_array($wrapperData) ? $wrapperData : [], is_string(...))), $body];
    }
}
