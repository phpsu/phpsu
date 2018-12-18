<?php
declare(strict_types=1);

namespace PHPSu\Alpha;

final class RsyncCommand implements CommandInterface
{
    /** @var SshConfig */
    private $sshConfig;
    /** @var string */
    private $options;

    /** @var string */
    private $fromHost = '';
    /** @var string */
    private $fromPath;

    /** @var string */
    private $toHost = '';
    /** @var string */
    private $toPath;

    /**
     * @param GlobalConfig $global
     * @param string $fromInstanceName
     * @param string $toInstanceName
     * @param string $currentHost
     * @return RsyncCommand[]
     */
    public static function fromGlobal(GlobalConfig $global, string $fromInstanceName, string $toInstanceName, string $currentHost): array
    {
        $fromInstance = $global->appInstances->{$fromInstanceName};
        $toInstance = $global->appInstances->{$toInstanceName};
        $result = [];
        foreach ($global->fileSystems as $fileSystemName => $fileSystem) {
            $result[] = static::fromAppInstances($fromInstance, $toInstance, $fileSystem, $currentHost);
        }
        return $result;
    }

    public static function fromAppInstances(AppInstance $from, AppInstance $to, string $filesystem, string $currentHost): RsyncCommand
    {
        $relPath = ($filesystem ? '/' : '') . $filesystem;

        $result = new static();
        $result->fromHost = $from->getHost() === $currentHost ? '' : $from->getHost();
        $result->toHost = $to->getHost() === $currentHost ? '' : $to->getHost();
        $result->fromPath = rtrim($from->getPath(), '/*') . $relPath . '/*';
        $result->toPath = rtrim($to->getPath(), '/') . $relPath . '/';
        return $result;
    }

    public function getSshConfig(): SshConfig
    {
        return $this->sshConfig;
    }

    public function setSshConfig(SshConfig $sshConfig): RsyncCommand
    {
        $this->sshConfig = $sshConfig;
        return $this;
    }

    public function getOptions(): string
    {
        return $this->options;
    }

    public function setOptions(string $options): RsyncCommand
    {
        $this->options = $options;
        return $this;
    }

    public function getFromHost(): string
    {
        return $this->fromHost;
    }

    public function setFromHost(string $fromHost): RsyncCommand
    {
        $this->fromHost = $fromHost;
        return $this;
    }

    public function getFromPath(): string
    {
        return $this->fromPath;
    }

    public function setFromPath(string $fromPath): RsyncCommand
    {
        $this->fromPath = $fromPath;
        return $this;
    }

    public function getToHost(): string
    {
        return $this->toHost;
    }

    public function setToHost(string $toHost): RsyncCommand
    {
        $this->toHost = $toHost;
        return $this;
    }

    public function getToPath(): string
    {
        return $this->toPath;
    }

    public function setToPath(string $toPath): RsyncCommand
    {
        $this->toPath = $toPath;
        return $this;
    }

    public function generate(): string
    {
        $hostsDifferentiate = $this->fromHost !== $this->toHost;
        $fromHostPart = '';
        $toHostPart = '';

        $command = 'rsync';
        if ($this->options) {
            $command .= ' ' . $this->options;
        }
        if ($hostsDifferentiate) {
            $file = $this->sshConfig->getFile();
            $command .= ' -e "ssh -F ' . $file->getPathname() . '"';
            $fromHostPart = $this->fromHost ? $this->fromHost . ':' : '';
            $toHostPart = $this->toHost ? $this->toHost . ':' : '';
        }
        $from = $fromHostPart . $this->fromPath;
        $to = $toHostPart . $this->toPath;
        $command .= ' ' . $from . ' ' . $to;

        if (!$hostsDifferentiate) {
            $sshCommand = new SshCommand();
            $sshCommand->setSshConfig($this->sshConfig);
            $sshCommand->setInto($this->fromHost);
            return $sshCommand->generate($command);
        }
        return $command;
    }
}
