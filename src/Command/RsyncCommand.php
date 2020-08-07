<?php

declare(strict_types=1);

namespace PHPSu\Command;

use PHPSu\Config\AppInstance;
use PHPSu\Config\FileSystem;
use PHPSu\Config\GlobalConfig;
use PHPSu\Config\SshConfig;
use PHPSu\Helper\StringHelper;
use PHPSu\ShellCommandBuilder\Exception\ShellBuilderException;
use PHPSu\ShellCommandBuilder\ShellBuilder;

/**
 * @internal
 */
final class RsyncCommand implements CommandInterface
{
    /** @var string */
    private $name;
    /** @var SshConfig */
    private $sshConfig;
    /** @var array<string> */
    private $shortOptions = ['az'];
    /** @var array<string> */
    private $options = [];

    /** @var string */
    private $sourceHost = '';
    /** @var string */
    private $sourcePath;

    /** @var string */
    private $destinationHost = '';
    /** @var string */
    private $toPath;
    /** @var array<string> */
    private $excludeList = [];
    /** @var string */
    private $verbosity = '';

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
        $result->verbosity = StringHelper::optionStringForVerbosity($verbosity);
        if (!$all) {
            foreach (array_unique(array_merge($sourceFilesystem->getExcludes(), $destinationFilesystem->getExcludes())) as $exclude) {
                $result->excludeList[] = $exclude;
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
        return implode(' ', array_merge($this->shortOptions, $this->options));
    }

    public function setOptions(string $options): RsyncCommand
    {
        $shortOptions = [];
        $list = [];
        foreach (explode(' ', $options) as $option) {
            $current = str_replace(['-', '--'], '', $option);
            if (strpos($option, '--') === 0) {
                $list[] = $current;
            } else {
                $shortOptions[] = $current;
            }
        }
        $this->shortOptions = $shortOptions;
        $this->options = $list;
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

    /**
     * @param ShellBuilder $shellBuilder
     * @return ShellBuilder
     * @throws ShellBuilderException
     */
    public function generate(ShellBuilder $shellBuilder): ShellBuilder
    {
        $hostsDifferentiate = $this->getSourceHost() !== $this->getDestinationHost();
        $fromHostPart = '';
        $toHostPart = '';

        $command = ShellBuilder::command('rsync');
        if ($this->verbosity) {
            $command->addShortOption($this->verbosity);
        }
        foreach ($this->shortOptions as $option) {
            $command->addShortOption(trim($option));
        }
        foreach ($this->options as $option) {
            $command->addOption(trim($option));
        }
        foreach ($this->excludeList as $option) {
            $command->addOption('exclude', $option);
        }
        if ($hostsDifferentiate) {
            $file = $this->sshConfig->getFile();
            $command->addShortOption(
                'e',
                ShellBuilder::command('ssh')
                ->addShortOption('F', $file->getPathname())
            );
            $fromHostPart = $this->getSourceHost() !== '' ? $this->getSourceHost() . ':' : '';
            $toHostPart = $this->getDestinationHost() !== '' ? $this->getDestinationHost() . ':' : '';
        }
        $from = $fromHostPart . $this->getSourcePath();
        $to = $toHostPart . $this->getToPath();
        $command->addArgument($from)->addArgument($to);

        if (!$hostsDifferentiate) {
            $sshCommand = new SshCommand();
            $sshCommand->setSshConfig($this->getSshConfig());
            $sshCommand->setInto($this->getSourceHost());
            return $sshCommand->generate($shellBuilder, $command);
        }
        return $shellBuilder->add($command);
    }
}
