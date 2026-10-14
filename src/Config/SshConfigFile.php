<?php

declare(strict_types=1);

namespace PHPSu\Config;

use InvalidArgumentException;

/**
 * An external ssh config file (local path or https url) that gets included into the generated ssh config
 * @api
 */
final class SshConfigFile implements ConfigElement
{
    public const DEFAULT_TTL = 600;

    private string $source;

    private int $ttl = self::DEFAULT_TTL;

    private ?string $username = null;

    private ?string $password = null;

    private bool $loaded = false;

    private ?string $path = null;

    /** @var string[] */
    private array $hosts = [];

    public function __construct(string $source)
    {
        $this->setSource($source);
    }

    public function getSource(): string
    {
        return $this->source;
    }

    public function setSource(string $source): SshConfigFile
    {
        if ($source === '') {
            throw new InvalidArgumentException('ssh config file source can not be empty');
        }

        if (preg_match('#^([a-z][a-z0-9+.-]*)://#i', $source, $matches) && strtolower($matches[1]) !== 'https') {
            throw new InvalidArgumentException(sprintf('ssh config file url %s must use https', $source));
        }

        $this->source = $source;
        return $this;
    }

    public function isUrl(): bool
    {
        return stripos($this->source, 'https://') === 0;
    }

    /**
     * time in seconds a downloaded ssh config file is cached
     */
    public function getTtl(): int
    {
        return $this->ttl;
    }

    public function setTtl(int $ttl): SshConfigFile
    {
        if ($ttl < 0) {
            throw new InvalidArgumentException(sprintf('ttl must be positive, %d given', $ttl));
        }

        $this->ttl = $ttl;
        return $this;
    }

    public function setBasicAuth(string $username, string $password): SshConfigFile
    {
        $this->username = $username;
        $this->password = $password;
        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * @internal
     */
    public function isLoaded(): bool
    {
        return $this->loaded;
    }

    /**
     * @internal
     * @param string[] $hosts
     */
    public function setLoaded(?string $path, array $hosts): SshConfigFile
    {
        $this->loaded = true;
        $this->path = $path;
        $this->hosts = $hosts;
        return $this;
    }

    /**
     * absolute path of the file that gets included, null if it could not be loaded
     * @internal
     */
    public function getPath(): ?string
    {
        return $this->path;
    }

    /**
     * @internal
     * @return string[]
     */
    public function getHosts(): array
    {
        return $this->hosts;
    }
}
