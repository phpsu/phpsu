<?php

declare(strict_types=1);

namespace PHPSu\Command;

use Exception;
use PHPSu\Config\Database;
use PHPSu\Config\GlobalConfig;
use PHPSu\Config\SshConfig;
use PHPSu\Helper\StringHelper;
use PHPSu\ShellCommandBuilder\Exception\ShellBuilderException;
use PHPSu\ShellCommandBuilder\ShellBuilder;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @internal
 * Class MysqlCommand
 * @package PHPSu\Command
 */
final class MysqlCommand implements CommandInterface
{
    /** @var null|string */
    private $command;
    /** @var SshConfig */
    private $sshConfig;
    /** @var Database */
    private $database;
    /** @var string */
    private $host;
    /** @var int */
    private $verbosity;

    /**
     * @param GlobalConfig $global
     * @param string $whichInstance
     * @param string|null $database
     * @param int $verbosity
     * @return MysqlCommand
     * @throws Exception
     */
    public static function fromGlobal(GlobalConfig $global, string $whichInstance, ?string $database = null, int $verbosity = OutputInterface::VERBOSITY_NORMAL): self
    {
        $command = new self();
        $command->setVerbosity($verbosity);
        $appInstance = $global->getAppInstance($whichInstance);
        // using global is a fallback for local where if databases are only defined globally
        $source = $appInstance->getDatabases() ? $appInstance : $global;
        if (!$database) {
            if (count($source->getDatabases()) > 1) {
                throw new Exception('There are multiple databases defined, please specify the one to connect to.');
            }
            $database = array_keys($source->getDatabases())[0];
        }
        $command->database = $source->getDatabase($database);
        $command->host = $appInstance->getHost();
        return $command;
    }

    /**
     * @param string|null $command
     * @return MysqlCommand
     */
    public function setCommand(?string $command): MysqlCommand
    {
        $this->command = $command;
        return $this;
    }

    public function setSshConfig(SshConfig $sshConfig): MysqlCommand
    {
        $this->sshConfig = $sshConfig;
        return $this;
    }

    public function setVerbosity(int $verbosity): MysqlCommand
    {
        $this->verbosity = $verbosity;
        return $this;
    }

    /**
     * @param ShellBuilder|null $shellBuilder
     * @return ShellBuilder
     * @throws ShellBuilderException
     * @throws Exception
     */
    public function generate(ShellBuilder $shellBuilder = null): ShellBuilder
    {
        $shellBuilder = $shellBuilder ?? ShellBuilder::new();
        $ssh = new SshCommand();
        $ssh->setVerbosity($this->verbosity);
        $ssh->setInto($this->host);
        $ssh->setSshConfig($this->sshConfig);
        $verbosity = StringHelper::optionStringForVerbosity($this->verbosity);
        $mysql = ShellBuilder::command('mysql');
        if ($verbosity) {
            $mysql->addShortOption($verbosity);
        }
        $mysql->addOption('user', $this->database->getConnectionDetails()->getUser(), true, true)
            ->addOption('password', $this->database->getConnectionDetails()->getPassword(), true, true)
            ->addOption('host', $this->database->getConnectionDetails()->getHost(), false, true)
            ->addOption('port', (string)$this->database->getConnectionDetails()->getPort(), false, true)
            ->addArgument($this->database->getConnectionDetails()->getDatabase())
        ;
        if ($this->command) {
            $mysql->addShortOption('e', $this->command);
        } else {
            $ssh->addOption('t', '', true);
        }

//      Disable autocomplete for faster mysql-connection
//      $mysql->addShortOption('A');
        $ssh->setCommand(DockerCommandHelper::wrapCommand($this->database, $mysql, empty($this->command)));
        return $ssh->generate($shellBuilder);
    }
}
