<?php
declare(strict_types=1);

namespace PHPSu\Alpha;

final class SshCmd
{
    /** @var SshConfig */
    private $sshConfig;
    /** @var string */
    private $into;

    public function getSshConfig(): SshConfig
    {
        return $this->sshConfig;
    }

    public function setSshConfig(SshConfig $sshConfig): SshCmd
    {
        $this->sshConfig = $sshConfig;
        return $this;
    }

    public function getInto(): string
    {
        return $this->into;
    }

    public function setInto(string $into): SshCmd
    {
        $this->into = $into;
        return $this;
    }
}
