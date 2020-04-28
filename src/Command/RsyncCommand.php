<?php

declare(strict_types=1);

namespace PHPSu\Command;

use PHPSu\Config\AppInstance;
use PHPSu\Config\FileSystem;
use PHPSu\Config\GlobalConfig;
use PHPSu\Config\SshConfig;
use PHPSu\Helper\StringHelper;

/**
 * @internal
 */
final class RsyncCommand implements CommandInterface
{
    /** @var string */
    private $name;
    /** @var SshConfig */
    private $sshConfig;
    /** @var string */
    private $options = '-az';

    /** @var string */
    private $sourceHost = '';
    /** @var string */
    private $sourcePath;

    /** @var string */
    private $destinationHost = '';
    /** @var string */
    private $toPath;

    /**
     * @param GlobalConfig $global
     * @param string $sourceInstanceName
     * @param string $destinationInstanceName
     * @param string $currentHost
     * @param bool $all
     * @param int $verbosity
     * @return RsyncCommand[]
     */
    public static function fromGlobal(GlobalConfig $global, string $sourceInstanceName, string $destinationInstanceName, string $currentHost, bool $all, int $verbosity): array
    {
        $sourceInstance = $global->getAppInstance($sourceInstanceName);
        $destinationInstance = $global->getAppInstance($destinationInstanceName);
        $result = [];
        foreach ($global->getFileSystems() as $fileSystemName => $fileSystem) {
            $fromFilesystem = $fileSystem;
            if ($sourceInstance->hasFilesystem($fileSystemName)) {
                $fromFilesystem = $sourceInstance->getFilesystem($fileSystemName);
            }
            $toFilesystem = $fileSystem;
            if ($destinationInstance->hasFilesystem($fileSystemName)) {
                $toFilesystem = $destinationInstance->getFilesystem($fileSystemName);
            }
            $result[] = static::fromAppInstances($sourceInstance, $destinationInstance, $fromFilesystem, $toFilesystem, $currentHost, $all, $verbosity);
        }
        return $result;
    }

    public static function fromAppInstances(AppInstance $source, AppInstance $destination, FileSystem $sourceFilesystem, FileSystem $destinationFilesystem, string $currentHost, bool $all, int $verbosity): RsyncCommand
    {
        $fromRelPath = ($sourceFilesystem->getPath() !== '' ? '/' : '') . $sourceFilesystem->getPath();
        $toRelPath = ($destinationFilesystem->getPath() !== '' ? '/' : '') . $destinationFilesystem->getPath();

        $result = new static();
        $result->setName('filesystem:' . $sourceFilesystem->getName());
        $result->setSourceHost($source->getHost() === $currentHost ? '' : $source->getHost());
        $result->setDestinationHost($destination->getHost() === $currentHost ? '' : $destination->getHost());
        $result->setSourcePath(rtrim($source->getPath() === '' ? '.' : $source->getPath(), '/*') . $fromRelPath . '/');
        $result->setToPath(rtrim($destination->getPath() === '' ? '.' : $destination->getPath(), '/') . $toRelPath . '/');
        $result->setOptions(StringHelper::optionStringForVerbosity($verbosity) . $result->getOptions());
        if (!$all) {
            $excludeOptions = '';
            foreach (array_unique(array_merge($sourceFilesystem->getExcludes(), $destinationFilesystem->getExcludes())) as $exclude) {
                $excludeOptions .= '--exclude=' . escapeshellarg($exclude) . ' ';
            }
            if ($excludeOptions !== '') {
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

    public function getSourceHost(): string
    {
        return $this->sourceHost;
    }

    public function setSourceHost(string $sourceHost): RsyncCommand
    {
        $this->sourceHost = $sourceHost;
        return $this;
    }

    public function getSourcePath(): string
    {
        return $this->sourcePath;
    }

    public function setSourcePath(string $sourcePath): RsyncCommand
    {
        $this->sourcePath = $sourcePath;
        return $this;
    }

    public function getDestinationHost(): string
    {
        return $this->destinationHost;
    }

    public function setDestinationHost(string $destinationHost): RsyncCommand
    {
        $this->destinationHost = $destinationHost;
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
        $hostsDifferentiate = $this->getSourceHost() !== $this->getDestinationHost();
        $fromHostPart = '';
        $toHostPart = '';

        $command = 'rsync';
        if ($this->getOptions() !== '') {
            $command .= ' ' . trim($this->getOptions());
        }
        if ($hostsDifferentiate) {
            $file = $this->sshConfig->getFile();
            $command .= ' -e ' . escapeshellarg('ssh -F ' . escapeshellarg($file->getPathname()));
            $fromHostPart = $this->getSourceHost() !== '' ? $this->getSourceHost() . ':' : '';
            $toHostPart = $this->getDestinationHost() !== '' ? $this->getDestinationHost() . ':' : '';
        }
        $from = $fromHostPart . $this->getSourcePath();
        $to = $toHostPart . $this->getToPath();
        $command .= ' ' . escapeshellarg($from) . ' ' . escapeshellarg($to);

        if (!$hostsDifferentiate) {
            $sshCommand = new SshCommand();
            $sshCommand->setSshConfig($this->getSshConfig());
            $sshCommand->setInto($this->getSourceHost());
            return $sshCommand->generate($command);
        }
        return $command;
    }
}
