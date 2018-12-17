<?php
declare(strict_types=1);

namespace PHPSu\Alpha;

final class DSN
{
    const DEFAULT_PORT = [
        'ssh' => 22,
        'mysql' => 3306,
    ];
    /** @var string */
    private $protocol;
    /** @var string */
    private $user;
    /** @var string */
    private $password;
    /** @var string */
    private $host;
    /** @var int */
    private $port;
    /** @var string */
    private $path;

    public function __construct(string $url, string $type)
    {
        if (!isset(static::DEFAULT_PORT[$type])) {
            throw new \Exception('DSN ' . $type . ' not supported');
        }
        if (!preg_match('/[a-zA-Z]+\:\/\//', $url)) {
            $url = $type . '://' . $url;
        }
        $result = parse_url($url);
        if (!$result) {
            throw new \Exception('DSN could not been parsed' . $url);
        }
        $this->protocol = $result['scheme'];
        $this->user = $result['user'] ?? '';
        $this->password = $result['pass'] ?? '';
        $this->host = $result['host'];
        $this->port = (int)($result['port'] ?? static::DEFAULT_PORT[$type]);
        $this->path = ltrim($result['path'] ?? '', '/');
    }

    public function getProtocol(): string
    {
        return $this->protocol;
    }

    public function getUser(): string
    {
        return $this->user;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function getPort(): int
    {
        return $this->port;
    }

    public function getPath(): string
    {
        return $this->path;
    }
}
