<?php
declare(strict_types=1);

namespace PHPSu\Alpha;

final class DatabaseCmd
{
    /** @var SshConfig */
    private $sshConfig;

    /** @var string */
    private $fromUrl;
    /** @var string */
    private $fromHost;

    /** @var string */
    private $toUrl;
    /** @var string */
    private $toHost;

    public function getSshConfig(): SshConfig
    {
        return $this->sshConfig;
    }

    public function setSshConfig(SshConfig $sshConfig): DatabaseCmd
    {
        $this->sshConfig = $sshConfig;
        return $this;
    }

    public function getFromUrl(): string
    {
        return $this->fromUrl;
    }

    public function setFromUrl(string $fromUrl): DatabaseCmd
    {
        $this->fromUrl = $fromUrl;
        return $this;
    }

    public function getFromHost(): string
    {
        return $this->fromHost;
    }

    public function setFromHost(string $fromHost): DatabaseCmd
    {
        $this->fromHost = $fromHost;
        return $this;
    }

    public function getToUrl(): string
    {
        return $this->toUrl;
    }

    public function setToUrl(string $toUrl): DatabaseCmd
    {
        $this->toUrl = $toUrl;
        return $this;
    }

    public function getToHost(): string
    {
        return $this->toHost;
    }

    public function setToHost(string $toHost): DatabaseCmd
    {
        $this->toHost = $toHost;
        return $this;
    }
}
