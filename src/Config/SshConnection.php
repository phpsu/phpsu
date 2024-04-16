<?php

declare(strict_types=1);

namespace PHPSu\Config;

use InvalidArgumentException;

/**
 * @api
 */
final class SshConnection implements ConfigElement
{
    private string $host;

    private SshUrl $url;

    /** @var string[] */
    private array $options = [];

    /** @var string[] */
    private array $from = [];

    public function getHost(): string
    {
        return $this->host;
    }

    public function setHost(string $host): SshConnection
    {
        if (str_contains($host, '/')) {
            throw new InvalidArgumentException(sprintf('host %s has invalid character', $host));
        }

        $this->host = $host;
        return $this;
    }

    public function getUrl(): SshUrl
    {
        return $this->url;
    }

    public function setUrl(string $url): SshConnection
    {
        $this->url = new SshUrl($url);
        return $this;
    }

    /**
     * @return string[]
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @param string[] $options
     */
    public function setOptions(array $options): SshConnection
    {
        $this->options = $options;
        return $this;
    }

    /**
     * @return string[]
     */
    public function getFrom(): array
    {
        return $this->from;
    }

    /**
     * @param string[] $from
     */
    public function setFrom(array $from): SshConnection
    {
        $this->from = $from;
        return $this;
    }
}
