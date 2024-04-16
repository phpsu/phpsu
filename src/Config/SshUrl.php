<?php

declare(strict_types=1);

namespace PHPSu\Config;

use Stringable;
use Exception;
use InvalidArgumentException;

/**
 * @api
 */
final class SshUrl implements Stringable
{
    private string $user;

    private string $password;

    private string $host;

    private int $port;

    public function __construct(string $url)
    {
        if (!preg_match('/[a-zA-Z]+\:\/\//', $url)) {
            $url = 'ssh://' . $url;
        }

        $result = parse_url($url);
        if (!$result) {
            throw new Exception('SshUrl could not been parsed: ' . $url);
        }

        $this->setUser($result['user'] ?? '');
        $this->setPassword($result['pass'] ?? '');
        $this->setHost($result['host'] ?? '');
        $this->setPort($result['port'] ?? 22);
    }

    public function getUser(): string
    {
        return $this->user;
    }

    public function setUser(string $user): SshUrl
    {
        if ($user === '') {
            throw new InvalidArgumentException('User must be set');
        }

        $this->user = $user;
        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): SshUrl
    {
        $this->password = $password;
        return $this;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function setHost(string $host): SshUrl
    {
        if (str_contains($host, '/')) {
            throw new InvalidArgumentException(sprintf('host %s has invalid character', $host));
        }

        if ($host === '') {
            throw new InvalidArgumentException('Host must be set');
        }

        $this->host = $host;
        return $this;
    }

    public function getPort(): int
    {
        return $this->port;
    }

    public function setPort(int $port): SshUrl
    {
        if ($port <= 0 || $port >= 65535) {
            throw new Exception('port must be between 0 and 65535');
        }

        $this->port = $port;
        return $this;
    }

    public function __toString(): string
    {
        $result = 'ssh://';
        $result .= $this->user;
        if ($this->password !== '') {
            $result .= ':' . $this->password;
        }

        $result .= '@' . $this->host;
        if ($this->port !== 22) {
            $result .= ':' . $this->port;
        }

        return $result;
    }
}
