<?php
declare(strict_types=1);

namespace PHPSu\Command;

use PHPSu\Config\AppInstance;
use PHPSu\Config\FileSystem;
use PHPSu\Config\GlobalConfig;
use PHPSu\Config\SshConfig;

final class RsyncCommand implements CommandInterface
{
    /** @var string */
    private $name;
    /** @var SshConfig */
    private $sshConfig;
    /** @var string */
    private $options = '-avz';

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
     * @param bool $all
     * @return RsyncCommand[]
     */
    public static function fromGlobal(GlobalConfig $global, string $fromInstanceName, string $toInstanceName, string $currentHost, bool $all): array
    {
        $fromInstance = $global->getAppInstance($fromInstanceName);
        $toInstance = $global->getAppInstance($toInstanceName);
        $result = [];
        foreach ($global->getFileSystems() as $fileSystemName => $fileSystem) {
            $fromFilesystem = $fileSystem;
            if ($fromInstance->hasFilesystem($fileSystemName)) {
                $fromFilesystem = $fromInstance->getFilesystem($fileSystemName);
            }
            $toFilesystem = $fileSystem;
            if ($toInstance->hasFilesystem($fileSystemName)) {
                $toFilesystem = $toInstance->getFilesystem($fileSystemName);
            }
            $result[] = static::fromAppInstances($fromInstance, $toInstance, $fromFilesystem, $toFilesystem, $currentHost, $all);
        }
        return $result;
    }

    public static function fromAppInstances(AppInstance $from, AppInstance $to, FileSystem $fromFilesystem, FileSystem $toFilesystem, string $currentHost, bool $all): RsyncCommand
    {
        $fromRelPath = ($fromFilesystem->getPath() ? '/' : '') . $fromFilesystem->getPath();
        $toRelPath = ($toFilesystem->getPath() ? '/' : '') . $toFilesystem->getPath();

        $result = new static();
        $result->setName('filesystem:' . $fromFilesystem->getName());
        $result->setFromHost($from->getHost() === $currentHost ? '' : $from->getHost());
        $result->setToHost($to->getHost() === $currentHost ? '' : $to->getHost());
        $result->setFromPath(rtrim($from->getPath() === '' ? '.' : $from->getPath(), '/*') . $fromRelPath . '/*');
        $result->setToPath(rtrim($to->getPath() === '' ? '.' : $to->getPath(), '/') . $toRelPath . '/');
        if ($all === false) {
            $excludeOptions = '';
            foreach (array_unique(array_merge($fromFilesystem->getExcludes(), $toFilesystem->getExcludes())) as $exclude) {
                $excludeOptions .= '--exclude=' . escapeshellarg($exclude) . ' ';
            }
            if ($excludeOptions) {
                $result->setOptions($result->getOptions() . ' ' . $excludeOptions);
            }
        }
        return $result;
    }

    public function setName(string $name): RsyncCommand
    {
        $this->name = $name;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
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
        $hostsDifferentiate = $this->getFromHost() !== $this->getToHost();
        $fromHostPart = '';
        $toHostPart = '';

        $command = 'rsync';
        if ($this->getOptions()) {
            $command .= ' ' . trim($this->getOptions());
        }
        if ($hostsDifferentiate) {
            $file = $this->sshConfig->getFile();
            $command .= ' -e ' . escapeshellarg('ssh -F ' . escapeshellarg($file->getPathname()));
            $fromHostPart = $this->getFromHost() ? $this->getFromHost() . ':' : '';
            $toHostPart = $this->getToHost() ? $this->getToHost() . ':' : '';
        }
        $from = $fromHostPart . $this->getFromPath();
        $to = $toHostPart . $this->getToPath();
        $command .= ' ' . escapeshellarg($from) . ' ' . escapeshellarg($to);

        if (!$hostsDifferentiate) {
            $sshCommand = new SshCommand();
            $sshCommand->setSshConfig($this->getSshConfig());
            $sshCommand->setInto($this->getFromHost());
            return $sshCommand->generate($command);
        }
        return $command;
    }
}
