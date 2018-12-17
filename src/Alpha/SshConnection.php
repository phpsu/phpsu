<?php
declare(strict_types=1);

namespace PHPSu\Alpha;

final class SshConnection
{
    /** @var string */
    private $host;
    /** @var string */
    private $url;
    /** @var string */
    private $identityFile = '';
    /** @var string[] */
    private $options = [];
    /** @var string[] */
    private $from = [];

    public function getHost(): string
    {
        return $this->host;
    }

    public function setHost(string $host): SshConnection
    {
        $this->host = $host;
        return $this;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): SshConnection
    {
        $this->url = $url;
        return $this;
    }

    public function getIdentityFile(): string
    {
        return $this->identityFile;
    }

    public function setIdentityFile(string $identityFile): SshConnection
    {
        $this->identityFile = $identityFile;
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
     * @return SshConnection
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
     * @return SshConnection
     */
    public function setFrom(array $from): SshConnection
    {
        $this->from = $from;
        return $this;
    }
}
