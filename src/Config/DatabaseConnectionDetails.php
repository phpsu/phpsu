<?php

declare(strict_types=1);

namespace PHPSu\Config;

use Exception;
use InvalidArgumentException;

/**
 * @internal
 */
final class DatabaseConnectionDetails
{
    /** @var string */
    private $user;
    /** @var string */
    private $password;
    /** @var string */
    private $host;
    /** @var int */
    private $port;
    /** @var string */
    private $database;

    private function __construct()
    {
    }

    public static function fromDetails(string $database, string $user = '', string $password = '', string $host = '127.0.0.1', int $port = 3306): self
    {
        $self = new self();
        $self->setUser($user);
        $self->setPassword($password);
        $self->setHost($host);
        $self->setPort($port);
        $self->setDatabase($database);
        return $self;
    }

    public static function fromUrlString(string $url): self
    {
        if (!preg_match('/[a-zA-Z]+\:\/\//', $url)) {
            $url = 'mysql://' . $url;
        }
        $result = parse_url($url);
        if (!$result) {
            throw new Exception('DatabaseUrl could not been parsed: ' . $url);
        }
        return self::fromDetails(
            ltrim($result['path'] ?? '', '/'),
            $result['user'] ?? '',
            $result['pass'] ?? '',
            $result['host'] ?? '',
            $result['port'] ?? 3306
        );
    }

    public function getUser(): string
    {
        return $this->user;
    }

    public function setUser(string $user): DatabaseConnectionDetails
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

    public function setPassword(string $password): DatabaseConnectionDetails
    {
        $this->password = $password;
        return $this;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function setHost(string $host): DatabaseConnectionDetails
    {
        if (strpos($host, '/') !== false) {
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

    public function setPort(int $port): DatabaseConnectionDetails
    {
        if ($port <= 0 || $port >= 65535) {
            throw new Exception('port must be between 0 and 65535');
        }
        $this->port = $port;
        return $this;
    }

    public function getDatabase(): string
    {
        return $this->database;
    }

    public function setDatabase(string $database): DatabaseConnectionDetails
    {
        $this->database = $database;
        return $this;
    }

    public function __toString(): string
    {
        $result = 'mysql://';
        $result .= $this->getUser();
        if ($this->getPassword() !== '') {
            $result .= ':' . $this->getPassword();
        }
        $result .= '@' . $this->getHost();
        if ($this->getPort() !== 3306) {
            $result .= ':' . $this->getPort();
        }
        return $result;
    }
}
