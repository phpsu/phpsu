<?php
declare(strict_types=1);

namespace PHPSu\Command;

use PHPSu\Config\GlobalConfig;
use PHPSu\Config\SshConfig;

final class SshCommand
{
    /** @var SshConfig */
    private $sshConfig;
    /** @var string */
    private $into;

    public static function fromGlobal(GlobalConfig $global, string $connectionName, string $currentHost): SshCommand
    {
        $host = $global->getHostName($connectionName);
        if ($currentHost === $host) {
            throw new \Exception(sprintf('the found host and the current Host are the same: %s', $host));
        }
        $result = new static();
        $result->setInto($host);
        return $result;
    }

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

    public function generate(string $command = ''): string
    {
        $file = $this->getSshConfig()->getFile();
        if ($this->getInto() === '') {
            return $command;
        }
        return 'ssh -F ' . $file->getPathname() . ' ' . $this->getInto() . ($command ? ' -C "' . addslashes($command) . '"' : '');
    }
}