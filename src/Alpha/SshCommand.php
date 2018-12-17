<?php
declare(strict_types=1);

namespace PHPSu\Alpha;

final class SshCommand implements CommandInterface
{
    /** @var SshConfig */
    private $sshConfig;
    /** @var string */
    private $into;

    public function getSshConfig(): SshConfig
    {
        return $this->sshConfig;
    }

    public function setSshConfig(SshConfig $sshConfig): SshCommand
    {
        $this->sshConfig = $sshConfig;
        return $this;
    }

    public function getInto(): string
    {
        return $this->into;
    }

    public function setInto(string $into): SshCommand
    {
        $this->into = $into;
        return $this;
    }

    public function generate(): string
    {
        $this->sshConfig->writeConfig($file = new \SplFileObject('.phpsu/config/ssh_config', 'rw+'));
        return 'ssh -F ' . $file->getPathname() . ' ' . $this->into;
    }
}
