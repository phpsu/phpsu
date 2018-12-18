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
    private $from;
    /** @var string */
    private $to;

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
        $fromHostPart = '';
        if ($from->getHost() !== $currentHost) {
            $fromHostPart = $from->getHost() . ':';
        }
        $toHostPart = '';
        if ($to->getHost() !== $currentHost) {
            $toHostPart = $to->getHost() . ':';
        }
        $result->from = $fromHostPart . rtrim($from->getPath(), '/*') . $relPath . '/*';
        $result->to = $toHostPart . rtrim($to->getPath(), '/') . $relPath . '/';
        /**
         * TODO if both source and destination are on the same system. than the data should flow between them and not through currentHost
         * aka: ssh host -C rsync $fromPath/$relPath/* $toPath/$relPath/*
         */
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

    public function getFrom(): string
    {
        return $this->from;
    }

    public function setFrom(string $from): RsyncCommand
    {
        $this->from = $from;
        return $this;
    }

    public function getTo(): string
    {
        return $this->to;
    }

    public function setTo(string $to): RsyncCommand
    {
        $this->to = $to;
        return $this;
    }

    public function generate(): string
    {
        $file = $this->sshConfig->getFile();
        return 'rsync ' . $this->options . ' -e "ssh -F ' . $file->getPathname() . '" ' . $this->from . ' ' . $this->to;
    }
}
