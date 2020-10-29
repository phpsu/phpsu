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
    /** @var SshConfig */
    private $sshConfig;
    /** @var string */
    private $into;
    /** @var string */
    private $path = '';
    /** @var int */
    private $verbosity = OutputInterface::VERBOSITY_NORMAL;
    /** @var ShellInterface|null */
    private $command = null;
    /** @var ShellCommand */
    private $shellCommand;

    public function __construct()
    {
        $this->shellCommand = ShellBuilder::command('ssh');
    }

    /**
     * @param GlobalConfig $global
     * @param string $connectionName
     * @param string $currentHost
     * @param int $verbosity
     * @return SshCommand
     * @throws Exception
     */
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

    public function setCommand(?ShellInterface $command): SshCommand
    {
        $this->command = $command;
        return $this;
    }

    /**
     * @param string $option
     * @param string|ShellInterface $value
     * @param bool $isShortOption
     * @param bool $escape
     * @param bool $useAssignOperator
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

    /**
     * @param ShellBuilder $shellBuilder
     * @return ShellBuilder
     * @throws ShellBuilderException|Exception
     */
    public function generate(ShellBuilder $shellBuilder): ShellBuilder
    {
        $command = $this->command;
        if ($this->getInto() === '') {
            return $command !== null ? $shellBuilder->add($command) : $shellBuilder;
        }
        $file = $this->getSshConfig()->getFile();
        $verbosity = StringHelper::optionStringForVerbosity($this->getVerbosity());
        $this->addOption($verbosity, '', true);
        $this->shellCommand->addShortOption('F', $file->getPathname())
            ->addArgument($this->getInto());
        if ($this->getPath() !== '') {
            if (empty($command) || empty($command->__toArray())) {
                // keep it interactive if no command is specified
                // todo: ShellBuilder needs to have a hasCommands method
                $command = ShellBuilder::command('bash')->addOption('login');
            }
            $this->shellCommand->addShortOption(
                't',
                ShellBuilder::new()
                    ->createCommand('cd')
                    ->addArgument($this->getPath())
                    ->addToBuilder()
                    ->add($command)
            );
        } elseif (!empty($command)) {
            $this->shellCommand->addArgument($command);
        }
        return $shellBuilder->add($this->shellCommand);
    }
}
