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
final class RsyncCommand implements CommandInterface, GroupedCommandInterface
{
    private string $name;

    private SshConfig $sshConfig;

    /** @var array<string> */
    private array $shortOptions = ['az'];

    /** @var array<string> */
    private array $options = [];

    private string $sourceHost = '';

    private string $sourcePath;

    private string $destinationHost = '';

    private string $toPath;

    /** @var array<string> */
    private array $excludeList = [];

    private string $verbosity = '';

    /**
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

            $result[] = self::fromAppInstances($sourceInstance, $destinationInstance, $fromFilesystem, $toFilesystem, $currentHost, $all, $verbosity);
        }

        return $result;
    }

    public static function fromAppInstances(AppInstance $source, AppInstance $destination, FileSystem $sourceFilesystem, FileSystem $destinationFilesystem, string $currentHost, bool $all, int $verbosity): RsyncCommand
    {
        $fromRelPath = ($sourceFilesystem->getPath() !== '' ? '/' : '') . $sourceFilesystem->getPath();
        $toRelPath = ($destinationFilesystem->getPath() !== '' ? '/' : '') . $destinationFilesystem->getPath();

        $result = new self();
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
            if (str_starts_with($option, '--')) {
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
     * @throws ShellBuilderException
     */
    public function generate(ShellBuilder $shellBuilder): ShellBuilder
    {
        $hostsDifferentiate = $this->sourceHost !== $this->destinationHost;
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
            $fromHostPart = $this->sourceHost !== '' ? $this->sourceHost . ':' : '';
            $toHostPart = $this->destinationHost !== '' ? $this->destinationHost . ':' : '';
        }

        $from = $fromHostPart . $this->sourcePath;
        $to = $toHostPart . $this->toPath;
        $command->addArgument($from)->addArgument($to);
        $command = DockerCommandHelper::wrapCommand(new FileSystem(), $command, false);

        if (!$hostsDifferentiate) {
            $sshCommand = new SshCommand();
            $sshCommand->setSshConfig($this->sshConfig);
            $sshCommand->setInto($this->sourceHost);
            $sshCommand->setCommand($command);
            return $sshCommand->generate($shellBuilder);
        }

        return $shellBuilder->add($command);
    }
}
