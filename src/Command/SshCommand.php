<?php

declare(strict_types=1);

namespace PHPSu\Command;

use Exception;
use PHPSu\Config\GlobalConfig;
use PHPSu\Config\SshConfig;
use PHPSu\Helper\StringHelper;
use PHPSu\ShellCommandBuilder\Exception\ShellBuilderException;
use PHPSu\ShellCommandBuilder\ShellBuilder;
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

    /**
     * @param ShellBuilder $shellBuilder
     * @param ShellInterface|null $command
     * @return ShellBuilder
     * @throws ShellBuilderException|Exception
     */
    public function generate(ShellBuilder $shellBuilder, ?ShellInterface $command = null): ShellBuilder
    {
        if ($this->getInto() === '') {
            return $shellBuilder->add($command);
        }
        $file = $this->getSshConfig()->getFile();
        $ssh = $shellBuilder->createCommand('ssh');
        $verbosity = StringHelper::optionStringForVerbosity($this->getVerbosity());
        if ($verbosity) {
            $ssh->addShortOption($verbosity);
        }
        $ssh->addShortOption('F', $file->getPathname())
            ->addArgument($this->getInto());
        if ($this->getPath() !== '') {
            if (empty($command) || empty($command->__toArray())) {
                // keep it interactive if no command is specified
                // todo: ShellBuilder needs to have a hasCommands method
                $command = ShellBuilder::command('bash')->addOption('login');
            }
            try {
                if (is_string($command)) {
                    var_dump($command);
                    die();
                }
                $ssh->addShortOption(
                    't',
                    ShellBuilder::new()
                        ->createCommand('cd')
                        ->addArgument($this->getPath())
                        ->addToBuilder()
                        ->add($command)
                );
            } catch (\Exception $exception) {
                var_dump($exception->getFile());
                die();
            }
        } elseif (!empty($command)) {
            $ssh->addArgument($command);
        }
        $ssh->addToBuilder();
        return $shellBuilder;
    }
}
