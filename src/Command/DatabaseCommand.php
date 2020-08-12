<?php

declare(strict_types=1);

namespace PHPSu\Command;

use GrumPHP\Task\Shell;
use PHPSu\Config\AppInstance;
use PHPSu\Config\Compression\CompressionInterface;
use PHPSu\Config\Compression\EmptyCompression;
use PHPSu\Config\Database;
use PHPSu\Config\DatabaseConnectionDetails;
use PHPSu\Config\GlobalConfig;
use PHPSu\Config\SshConfig;
use PHPSu\Helper\StringHelper;
use PHPSu\ShellCommandBuilder\Exception\ShellBuilderException;
use PHPSu\ShellCommandBuilder\ShellBuilder;
use PHPSu\ShellCommandBuilder\ShellCommand;
use Symfony\Component\Console\Output\OutputInterface;

use function get_class;
use function strlen;

/**
 * @internal
 */
final class DatabaseCommand implements CommandInterface
{
    /** @var string */
    private $name;
    /** @var SshConfig */
    private $sshConfig;
    /** @var string[] */
    private $excludes = [];

    /** @var DatabaseConnectionDetails */
    private $fromConnectionDetails;
    /** @var string */
    private $fromHost;

    /** @var DatabaseConnectionDetails */
    private $toConnectionDetails;
    /** @var string */
    private $toHost;

    /** @var int */
    private $verbosity = OutputInterface::VERBOSITY_NORMAL;

    /** @var CompressionInterface */
    private $compression;

    public function __construct()
    {
        $this->setCompression(new EmptyCompression());
    }

    /**
     * @param GlobalConfig $global
     * @param string $fromInstanceName
     * @param string $toInstanceName
     * @param string $currentHost
     * @param bool $all
     * @param int $verbosity
     * @return DatabaseCommand[]
     */
    public static function fromGlobal(
        GlobalConfig $global,
        string $fromInstanceName,
        string $toInstanceName,
        string $currentHost,
        bool $all,
        int $verbosity
    ): array {
        $fromInstance = $global->getAppInstance($fromInstanceName);
        $toInstance = $global->getAppInstance($toInstanceName);

        $compression = static::getCompressionOverlap($fromInstance, $toInstance);

        $result = [];
        foreach ($global->getDatabases() as $databaseName => $database) {
            $fromDatabase = $database;
            if ($fromInstance->hasDatabase($databaseName)) {
                $fromDatabase = $fromInstance->getDatabase($databaseName);
            }
            $toDatabase = $database;
            if ($toInstance->hasDatabase($databaseName)) {
                $toDatabase = $toInstance->getDatabase($databaseName);
            }
            $result[] = static::fromAppInstances($fromInstance, $toInstance, $fromDatabase, $toDatabase, $currentHost, $all, $verbosity, $compression);
        }
        foreach ($fromInstance->getDatabases() as $databaseName => $fromDatabase) {
            if ($toInstance->hasDatabase($databaseName)) {
                $toDatabase = $toInstance->getDatabase($databaseName);
                $result[] = static::fromAppInstances($fromInstance, $toInstance, $fromDatabase, $toDatabase, $currentHost, $all, $verbosity, $compression);
            }
        }
        return $result;
    }

    public static function fromAppInstances(
        AppInstance $from,
        AppInstance $to,
        Database $fromDatabase,
        Database $toDatabase,
        string $currentHost,
        bool $all,
        int $verbosity,
        CompressionInterface $compression
    ): DatabaseCommand {
        $result = new static();
        $result->setName('database:' . $fromDatabase->getName());
        $result->setFromHost($from->getHost() === $currentHost ? '' : $from->getHost());
        $result->setToHost($to->getHost() === $currentHost ? '' : $to->getHost());
        $result->setFromConnectionDetails($fromDatabase->getConnectionDetails());
        $result->setToConnectionDetails($toDatabase->getConnectionDetails());
        $result->setVerbosity($verbosity);
        $result->setCompression($compression);
        if (!$all) {
            $result->setExcludes(array_unique(array_merge($fromDatabase->getExcludes(), $toDatabase->getExcludes())));
        }
        return $result;
    }

    private static function getCompressionOverlap(AppInstance $fromInstance, AppInstance $toInstance): CompressionInterface
    {
        foreach ($fromInstance->getCompressions() as $fromCompression) {
            foreach ($toInstance->getCompressions() as $toCompression) {
                if (get_class($fromCompression) === get_class($toCompression)) {
                    return $fromCompression;
                }
            }
        }
        return new EmptyCompression();
    }


    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): DatabaseCommand
    {
        $this->name = $name;
        return $this;
    }

    public function getSshConfig(): SshConfig
    {
        return $this->sshConfig;
    }

    public function setSshConfig(SshConfig $sshConfig): DatabaseCommand
    {
        $this->sshConfig = $sshConfig;
        return $this;
    }

    /**
     * @return string[]
     */
    public function getExcludes(): array
    {
        return $this->excludes;
    }

    /**
     * @param string[] $excludes
     * @return DatabaseCommand
     */
    public function setExcludes(array $excludes): DatabaseCommand
    {
        $this->excludes = $excludes;
        return $this;
    }

    public function getFromConnectionDetails(): DatabaseConnectionDetails
    {
        return $this->fromConnectionDetails;
    }

    public function setFromConnectionDetails(DatabaseConnectionDetails $fromConnectionDetails): DatabaseCommand
    {
        $this->fromConnectionDetails = $fromConnectionDetails;
        return $this;
    }

    public function getFromHost(): string
    {
        return $this->fromHost;
    }

    public function setFromHost(string $fromHost): DatabaseCommand
    {
        $this->fromHost = $fromHost;
        return $this;
    }

    public function getToConnectionDetails(): DatabaseConnectionDetails
    {
        return $this->toConnectionDetails;
    }

    public function setToConnectionDetails(DatabaseConnectionDetails $toConnectionDetails): DatabaseCommand
    {
        $this->toConnectionDetails = $toConnectionDetails;
        return $this;
    }

    public function getToHost(): string
    {
        return $this->toHost;
    }

    public function setToHost(string $toHost): DatabaseCommand
    {
        $this->toHost = $toHost;
        return $this;
    }

    public function getVerbosity(): int
    {
        return $this->verbosity;
    }

    public function setVerbosity(int $verbosity): DatabaseCommand
    {
        $this->verbosity = $verbosity;
        return $this;
    }

    public function getCompression(): CompressionInterface
    {
        return $this->compression;
    }

    public function setCompression(CompressionInterface $compression): DatabaseCommand
    {
        $this->compression = $compression;
        return $this;
    }

    /**
     * @param ShellCommand $command
     * @param DatabaseConnectionDetails $connectionDetails
     * @param bool $excludeDatabase
     * @return ShellCommand
     * @throws ShellBuilderException
     */
    private function addArgumentsToShellCommand(ShellCommand $command, DatabaseConnectionDetails $connectionDetails, bool $excludeDatabase): ShellCommand
    {
        $command->addOption('host', $connectionDetails->getHost(), true, true);
        if ($connectionDetails->getPort() !== 3306) {
            $command->addOption('port', (string)$connectionDetails->getPort(), false, true);
        }
        $command->addOption('user', $connectionDetails->getUser(), true, true)
            ->addOption('password', $connectionDetails->getPassword(), true, true);

        if (!$excludeDatabase) {
            $command->addArgument($connectionDetails->getDatabase());
        }
        return $command;
    }

    /**
     * @param ShellBuilder $shellBuilder
     * @return ShellBuilder
     * @throws ShellBuilderException
     */
    public function generate(ShellBuilder $shellBuilder): ShellBuilder
    {
        $hostsDifferentiate = $this->getFromHost() !== $this->getToHost();
        $from = $this->getFromConnectionDetails();
        $to = $this->getToConnectionDetails();
        $tableInfo = false;
        $dbBuilder = ShellBuilder::new();
        if ($this->getExcludes()) {
            $sqlPart = $this->generateSqlQuery($from->getDatabase(), $this->getExcludes());
            $command = $this->addArgumentsToShellCommand(
                ShellBuilder::command('mysql'),
                $from,
                true
            )
                ->addShortOption('AN')
                ->addShortOption('e', sprintf('"%s"', $sqlPart), false)
            ;
            $dbBuilder->addVariable(
                'TBLIST',
                $command,
                true,
                true,
                true
            );
            $tableInfo = true;
        }
        $dumpCommand = ShellBuilder::command('mysqldump');
        $verbosity = StringHelper::optionStringForVerbosity($this->getVerbosity());
        if ($verbosity) {
            $dumpCommand->addShortOption($verbosity);
        }
        $dumpCommand = $this->addArgumentsToShellCommand(
            $dumpCommand
                ->addOption('opt')
                ->addOption('skip-comments')
                ->addOption('single-transaction')
                ->addOption('lock-tables', 'false', false, true),
            $from,
            false
        );
        $dumpBuilder = ShellBuilder::new();
        if ($tableInfo) {
            $dumpCommand->addArgument('${TBLIST}', false);
            $dumpBuilder
                ->add($dbBuilder)
                ->and($dumpCommand);
        } else {
            $dumpBuilder->add($dumpCommand);
        }
        $dumpBuilder
            ->pipe(
                $dumpBuilder->createGroup()
                ->createCommand('echo')
                ->addArgument($this->getDatabaseCreateStatement($to->getDatabase()))
                ->addToBuilder()
                ->and('cat')
            );
        $compressCmd = $this->getCompression()->getCompressCommand();
        $unCompressCmd = $this->getCompression()->getUnCompressCommand();
        $importCommand = $this->addArgumentsToShellCommand(
            ShellBuilder::command('mysql'),
            $to,
            true
        );
        if ($hostsDifferentiate) {
            if ($this->getFromHost() !== '') {
                $sshCommand = new SshCommand();
                $sshCommand->setSshConfig($this->getSshConfig());
                $sshCommand->setInto($this->getFromHost());
                $sshCommand->setVerbosity($this->getVerbosity());
                if ($compressCmd) {
                    $dumpBuilder->pipe($compressCmd);
                }
                $sshCommand->generate($shellBuilder, $dumpBuilder);
            } elseif ($compressCmd) {
                $dumpBuilder->pipe($compressCmd);
            }
            $importBuilder = ShellBuilder::new();
            if ($this->getToHost() !== '') {
                $sshCommand = new SshCommand();
                $sshCommand->setSshConfig($this->getSshConfig());
                $sshCommand->setInto($this->getToHost());
                $sshCommand->setVerbosity($this->getVerbosity());
                if ($unCompressCmd) {
                    $importBuilder->add($unCompressCmd)->pipe($importCommand);
                } else {
                    $importBuilder->add($importCommand);
                }
                $importBuilder = $sshCommand->generate(ShellBuilder::new(), $importBuilder);
            } elseif ($unCompressCmd) {
                $importBuilder->add($unCompressCmd)->pipe($importCommand);
            } else {
                $importBuilder->add($importCommand);
            }
            if (empty($shellBuilder->__toArray())) {
                $shellBuilder->add($dumpBuilder);
            }
            return $shellBuilder->pipe($importBuilder);
        }
        $sshCommand = new SshCommand();
        $sshCommand->setSshConfig($this->getSshConfig());
        $sshCommand->setInto($this->getFromHost());
        $sshCommand->setVerbosity($this->getVerbosity());
        return $sshCommand->generate($shellBuilder, $dumpBuilder->pipe($importCommand));
    }

    private function getDatabaseCreateStatement(string $targetDatabase): string
    {
        $escapedTargetDatabase = '`' . str_replace('`', '``', $targetDatabase) . '`';
        return sprintf('CREATE DATABASE IF NOT EXISTS %s;USE %s;', $escapedTargetDatabase, $escapedTargetDatabase);
    }

    /**
     * @param string[] $excludes
     * @return string
     */
    private function getExcludeSqlPart(array $excludes): string
    {
        $result = '';
        $simpleExclude = [];
        foreach ($excludes as $exclude) {
            $stringLength = strlen($exclude);
            // can be replaced in php 8.0 with str_starts_with and str_end_with
            if ($stringLength >= 3 && strncmp($exclude, '/', 1) === 0 && $exclude[$stringLength - 1] === '/') {
                $result .= ' AND table_name NOT REGEXP \'' . substr($exclude, 1, $stringLength - 2) . '\'';
            } else {
                $simpleExclude[] = '\'' . $exclude . '\'';
            }
        }
        if ($simpleExclude) {
            $result .= ' AND table_name NOT IN(' . implode(',', $simpleExclude) . ')';
        }
        return $result;
    }

    /**
     * @param string $database
     * @param string[] $excludes
     * @return string
     */
    private function generateSqlQuery(string $database, array $excludes): string
    {
        $whereCondition = $this->getExcludeSqlPart($excludes);
        return <<<SQL
SET group_concat_max_len = 51200; SELECT GROUP_CONCAT(table_name separator ' ') FROM information_schema.tables WHERE table_schema='${database}'${whereCondition}
SQL;
    }
}
