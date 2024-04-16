<?php

declare(strict_types=1);

namespace PHPSu\Command;

use Exception;
use PHPSu\Config\GlobalConfig;
use PHPSu\Config\SshConfig;
use PHPSu\Helper\StringHelper;
use PHPSu\ShellCommandBuilder\Exception\ShellBuilderException;
use PHPSu\ShellCommandBuilder\ShellBuilder;
use PHPSu\ShellCommandBuilder\ShellCommand;
use PHPSu\ShellCommandBuilder\ShellInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @internal
 */
final class SshCommand
{
    private SshConfig $sshConfig;

    private string $into;

    private string $path = '';

    private int $verbosity = OutputInterface::VERBOSITY_NORMAL;

    private ?ShellInterface $command = null;

    private readonly ShellCommand $shellCommand;

    public function __construct()
    {
        $this->shellCommand = ShellBuilder::command('ssh');
    }

    public static function fromGlobal(GlobalConfig $global, string $connectionName, string $currentHost, int $verbosity): SshCommand
    {
        $host = $global->getHostName($connectionName);
        if ($currentHost === $host) {
            throw new Exception(sprintf('the found host and the current Host are the same: %s', $host));
        }

        $result = new self();
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

    public function setCommand(?ShellInterface $command): SshCommand
    {
        $this->command = $command;
        return $this;
    }

    /**
     * @param string|ShellInterface $value
     * @return $this
     * @throws ShellBuilderException
     */
    public function addOption(string $option, $value = '', bool $isShortOption = false, bool $escape = true, bool $useAssignOperator = false): self
    {
        if ($option) {
            $args = [$option, $value, $escape, $useAssignOperator];
            $isShortOption ? $this->shellCommand->addShortOption(...$args) : $this->shellCommand->addOption(...$args);
        }

        return $this;
    }

    public function generate(ShellBuilder $shellBuilder): ShellBuilder
    {
        $command = $this->command;
        if ($this->into === '') {
            return $command !== null ? $shellBuilder->add($command) : $shellBuilder;
        }

        $file = $this->sshConfig->getFile();
        $verbosity = StringHelper::optionStringForVerbosity($this->verbosity);
        $this->addOption($verbosity, '', true);
        $this->shellCommand->addShortOption('F', $file->getPathname())
            ->addArgument($this->into);
        if ($this->path !== '') {
            if (!$command instanceof ShellInterface || $command->__toArray() === []) {
                // keep it interactive if no command is specified
                // todo: ShellBuilder needs to have a hasCommands method
                $command = ShellBuilder::command('bash')->addOption('login');
            }

            $this->shellCommand->addShortOption(
                't',
                ShellBuilder::new()
                    ->createCommand('cd')
                    ->addArgument($this->path)
                    ->addToBuilder()
                    ->add($command)
            );
        } elseif ($command instanceof ShellInterface) {
            $this->shellCommand->addArgument($command);
        }

        return $shellBuilder->add($this->shellCommand);
    }
}
