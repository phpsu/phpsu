<?php
declare(strict_types=1);

namespace PHPSu\Config;

use PHPSu\Exceptions\ConfigurationException;

final class SqlDatabaseConfiguration implements DatabaseConfigurationInterface
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

    public function __construct(string $url)
    {
        if (!preg_match('/[a-zA-Z]+\:\/\//', $url)) {
            $url = 'mysql://' . $url;
        }
        $result = parse_url($url);
        if (!$result) {
            throw new ConfigurationException('SqlDatabaseConfiguration could not been parsed: ' . $url);
        }
        $this->setUser($result['user'] ?? '');
        $this->setPassword($result['pass'] ?? '');
        $this->setHost($result['host'] ?? '');
        $this->setPort($result['port'] ?? 3306);
        $this->setDatabase(ltrim($result['path'] ?? '', '/'));
    }

    public function getUser(): string
    {
        return $this->user;
    }

    public function setUser(string $user): SqlDatabaseConfiguration
    {
        if (!$user) {
            throw new \InvalidArgumentException('User must be set');
        }
        $this->user = $user;
        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): SqlDatabaseConfiguration
    {
        $this->password = $password;
        return $this;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function setHost(string $host): SqlDatabaseConfiguration
    {
        if (strpos($host, '/') !== false) {
            throw new \InvalidArgumentException(sprintf('host %s has invalid character', $host));
        }
        if (!$host) {
            throw new \InvalidArgumentException('Host must be set');
        }
        $this->host = $host;
        return $this;
    }

    public function getPort(): int
    {
        return $this->port;
    }

    public function setPort(int $port): SqlDatabaseConfiguration
    {
        if ($port <= 0 || $port >= 65535) {
            throw new ConfigurationException('port must be between 0 and 65535');
        }
        $this->port = $port;
        return $this;
    }

    public function getDatabase(): string
    {
        return $this->database;
    }

    public function setDatabase(string $database): SqlDatabaseConfiguration
    {
        $this->database = $database;
        return $this;
    }

    public function __toString(): string
    {
        $result = 'mysql://';
        $result .= $this->getUser();
        if ($this->getPassword()) {
            $result .= ':' . $this->getPassword();
        }
        $result .= '@' . $this->getHost();
        if ($this->getPort() !== 3306) {
            $result .= ':' . $this->getPort();
        }
        return $result;
    }
}
