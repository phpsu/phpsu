<?php
declare(strict_types=1);

namespace PHPSu\Alpha;

final class RsyncCmd
{
    /** @var SshConfig */
    private $sshConfig;
    /** @var string */
    private $options;
    /** @var string */
    private $from;
    /** @var string */
    private $to;

    public function getSshConfig(): SshConfig
    {
        return $this->sshConfig;
    }

    public function setSshConfig(SshConfig $sshConfig): RsyncCmd
    {
        $this->sshConfig = $sshConfig;
        return $this;
    }

    public function getOptions(): string
    {
        return $this->options;
    }

    public function setOptions(string $options): RsyncCmd
    {
        $this->options = $options;
        return $this;
    }

    public function getFrom(): string
    {
        return $this->from;
    }

    public function setFrom(string $from): RsyncCmd
    {
        $this->from = $from;
        return $this;
    }

    public function getTo(): string
    {
        return $this->to;
    }

    public function setTo(string $to): RsyncCmd
    {
        $this->to = $to;
        return $this;
    }
}
