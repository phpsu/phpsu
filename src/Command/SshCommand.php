<?php

declare(strict_types=1);

namespace PHPSu\Command;

use Exception;
use PHPSu\Config\GlobalConfig;
use PHPSu\Config\SshConfig;
use PHPSu\Helper\StringHelper;
use Symfony\Component\Console\Output\OutputInterface;

final class SshCommand
{
    /** @var SshConfig */
    private $sshConfig;
    /** @var string */
    private $into;
    /** @var string */
    private $path = '';
    /** @var int */
    private $verbosity = OutputInterface::VERBOSITY_NORMAL;

    public static function fromGlobal(GlobalConfig $global, string $connectionName, string $currentHost, int $verbosity): SshCommand
    {
        $host = $global->getHostName($connectionName);
        if ($currentHost === $host) {
            throw new Exception(sprintf('the found host and the current Host are the same: %s', $host));
        }
        $result = new static();
        $result->setInto($host);
        $result->setVerbosity($verbosity);
        if (isset($global->getAppInstances()[$connectionName])) {
            $appInstance = $global->getAppInstances()[$connectionName];
            $result->setPath($appInstance->getPath());
        }
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

    public function getPath(): string
    {
        return $this->path;
    }

    public function setPath(string $path): SshCommand
    {
        $this->path = $path;
        return $this;
    }

    public function getVerbosity(): int
    {
        return $this->verbosity;
    }

    public function setVerbosity(int $verbosity): SshCommand
    {
        $this->verbosity = $verbosity;
        return $this;
    }

    public function generate(string $command = ''): string
    {
        $file = $this->getSshConfig()->getFile();
        if ($this->getInto() === '') {
            return $command;
        }
        $result = 'ssh ' . StringHelper::optionStringForVerbosity($this->getVerbosity()) . '-F ' . escapeshellarg($file->getPathname()) . ' ' . escapeshellarg($this->getInto());
        if ($this->getPath() !== '') {
            if ($command === '') {
                //keep it interactive if no command is specified
                $command = 'bash --login';
            }
            $result .= ' -t ' . escapeshellarg('cd ' . escapeshellarg($this->getPath()) . '; ' . $command);
        } elseif ($command !== '') {
            $result .= ' ' . escapeshellarg($command);
        }
        return $result;
    }
}
