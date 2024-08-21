<?php

declare(strict_types=1);

namespace PHPSu\Command;

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
use PHPSu\ShellCommandBuilder\ShellInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function get_class;
use function strlen;

/**
 * @internal
 */
final class DatabaseCommand implements CommandInterface, GroupedCommandInterface
{
    private string $name;

    private SshConfig $sshConfig;

    /** @var string[] */
    private array $excludes = [];

    private Database $fromDatabase;

    private string $fromHost;

    private Database $toDatabase;

    private string $toHost;

    private int $verbosity = OutputInterface::VERBOSITY_NORMAL;

    private CompressionInterface $compression;

    public function __construct()
    {
        $this->setCompression(new EmptyCompression());
    }

    /**
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

        $compression = self::getCompressionOverlap($fromInstance, $toInstance);

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

            $result[] = self::fromAppInstances($fromInstance, $toInstance, $fromDatabase, $toDatabase, $currentHost, $all, $verbosity, $compression);
        }

        foreach ($fromInstance->getDatabases() as $databaseName => $fromDatabase) {
            if ($toInstance->hasDatabase($databaseName)) {
                $toDatabase = $toInstance->getDatabase($databaseName);
                $result[] = self::fromAppInstances($fromInstance, $toInstance, $fromDatabase, $toDatabase, $currentHost, $all, $verbosity, $compression);
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
        $result = new self();
        $result->setName('database:' . $fromDatabase->getName());
        $result->setFromHost($from->getHost() === $currentHost ? '' : $from->getHost());
        $result->setToHost($to->getHost() === $currentHost ? '' : $to->getHost());
        $result->setFromDatabase($fromDatabase);
        $result->setToDatabase($toDatabase);
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
                if ($fromCompression::class === $toCompression::class) {
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
     */
    public function setExcludes(array $excludes): DatabaseCommand
    {
        $this->excludes = $excludes;
        return $this;
    }

    public function getFromDatabase(): Database
    {
        return $this->fromDatabase;
    }

    public function setFromDatabase(Database $fromDatabase): DatabaseCommand
    {
        $this->fromDatabase = $fromDatabase;
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

    public function getToDatabase(): Database
    {
        return $this->toDatabase;
    }

    public function setToDatabase(Database $toDatabase): DatabaseCommand
    {
        $this->toDatabase = $toDatabase;
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

    public function generate(ShellBuilder $shellBuilder): ShellBuilder
    {
        $hostsDifferentiate = $this->fromHost !== $this->toHost;
        $from = $this->fromDatabase->getConnectionDetails();
        $to = $this->toDatabase->getConnectionDetails();
        $tableInfo = false;
        $dbBuilder = ShellBuilder::new();
        if ($this->excludes) {
            $sqlPart = $this->generateSqlQuery($from->getDatabase(), $this->excludes);
            $command = DockerCommandHelper::wrapCommand(
                $this->fromDatabase,
                $this->addArgumentsToShellCommand(
                    ShellBuilder::command($from->getDatabaseType() === 'mysql' ? 'mysql' : 'mariadb'),
                    $from,
                    true
                )
                    ->addShortOption('AN')
                    ->addShortOption('e', sprintf('"%s"', $sqlPart), false),
                false
            );
            $dbBuilder->addVariable(
                'TBLIST',
                $command,
                true,
                true,
                true
            );
            $tableInfo = true;
        }

        $dumpCommand = ShellBuilder::command($from->getDatabaseType() === 'mysql' ? 'mysqldump' : 'mariadb-dump');
        $verbosity = StringHelper::optionStringForVerbosity($this->verbosity);
        if ($verbosity) {
            $dumpCommand->addShortOption($verbosity);
        }

        $dumpCommand = $this->addArgumentsToShellCommand(
            $dumpCommand
                ->addOption('opt')
                ->addOption('skip-comments')
                ->addOption('single-transaction')
                ->addOption('lock-tables', 'false', false, true)
                ->addOption('no-tablespaces')
                ->addOption('complete-insert'),
            $from,
            false
        );
        $dumpBuilder = ShellBuilder::new();
        if ($tableInfo) {
            $dumpCommand->addArgument('${TBLIST}', false);
            $dumpCommand = DockerCommandHelper::wrapCommand(
                $this->fromDatabase,
                $dumpCommand,
                false,
                ['TBLIST' => '${TBLIST}']
            );
            $dumpBuilder
                ->add($dbBuilder)
                ->and($dumpCommand);
        } else {
            $dumpBuilder->add(DockerCommandHelper::wrapCommand($this->fromDatabase, $dumpCommand, false));
        }

        $dumpBuilder
            ->pipe(
                $dumpBuilder->createGroup()
                    ->createCommand('echo')
                    ->addArgument($this->getDatabaseCreateStatement($to->getDatabase()))
                    ->addToBuilder()
                    ->and('cat')
            );
        $removeSandboxModeCommand = $this->getRemoveSandboxModeCommand();
        $removeDefinerCommand = $this->getRemoveDefinerCommand();
        $compressCmd = $this->compression->getCompressCommand();
        $unCompressCmd = $this->compression->getUnCompressCommand();
        $importCommand = $this->addArgumentsToShellCommand(
            ShellBuilder::command('mysql'),
            $to,
            true
        );
        $dumpBuilder
            ->if(
                !$this->fromDatabase->shouldAllowSandboxMode(),
                static fn(ShellBuilder $builder): ShellBuilder => $builder->pipe($removeSandboxModeCommand)
            )
            ->if(
                $this->fromDatabase->shouldDefinerBeRemoved(),
                static fn(ShellBuilder $builder): ShellBuilder => $builder->pipe($removeDefinerCommand)
            )
            ->if($compressCmd !== '' && $compressCmd !== '0', static fn(ShellBuilder $builder): ShellBuilder => $builder->pipe($compressCmd));
        if ($hostsDifferentiate) {
            if ($this->fromHost !== '') {
                $sshCommand = new SshCommand();
                $sshCommand->setSshConfig($this->sshConfig);
                $sshCommand->setInto($this->fromHost);
                $sshCommand->setVerbosity($this->verbosity);
                $sshCommand->setCommand($this->prependPipefail($dumpBuilder));
                $sshCommand->generate($shellBuilder);
            }

            $importBuilder = ShellBuilder::new();
            $importCommand = DockerCommandHelper::wrapCommand($this->toDatabase, $importCommand, false);
            if ($this->toHost !== '') {
                $importBuilder = (new SshCommand())
                    ->setSshConfig($this->sshConfig)
                    ->setInto($this->toHost)
                    ->setVerbosity($this->verbosity)
                    ->setCommand(
                        $this->prependPipefail(
                            $importBuilder->if(
                                $unCompressCmd !== '' && $unCompressCmd !== '0',
                                static fn(ShellBuilder $builder): ShellBuilder => $builder->add($unCompressCmd)->pipe($importCommand),
                                static fn(ShellBuilder $builder): ShellBuilder => $builder->add($importCommand)
                            )
                        )
                    )
                    ->generate(ShellBuilder::new());
            } elseif ($unCompressCmd) {
                $importBuilder->add($unCompressCmd)->pipe($importCommand);
            } else {
                $importBuilder->add($importCommand);
            }

            $dumpBuilderPackagedIfNeeded = $shellBuilder->if(
                $shellBuilder->__toArray() === [],
                static fn(ShellBuilder $builder): ShellBuilder => $builder->add($dumpBuilder)
            );
            return $this->prependPipefail($dumpBuilderPackagedIfNeeded->pipe($importBuilder));
        }

        if (!$this->fromHost) {
            return $this->prependPipefail($dumpBuilder->pipe($importCommand));
        }

        $sshCommand = new SshCommand();
        $sshCommand->setSshConfig($this->sshConfig);
        $sshCommand->setInto($this->fromHost);
        $sshCommand->setVerbosity($this->verbosity);
        $sshCommand->setCommand($this->prependPipefail($dumpBuilder->pipe($importCommand)));
        return $this->prependPipefail($sshCommand->generate($shellBuilder));
    }

    public function prependPipefail(ShellInterface $shell): ShellBuilder
    {
        return ShellBuilder::command('set')->addShortOption('o', 'pipefail', false)->addToBuilder()->and($shell);
    }

    private function getRemoveDefinerCommand(): ShellInterface
    {
        return ShellBuilder::command('sed')
            ->addShortOption(
                'e',
                's/DEFINER[ ]*=[ ]*[^*]*\*/\*/; s/DEFINER[ ]*=[ ]*[^*]*PROCEDURE/PROCEDURE/; s/DEFINER[ ]*=[ ]*[^*]*FUNCTION/FUNCTION/'
            );
    }

    private function getRemoveSandboxModeCommand(): ShellInterface
    {
        return ShellBuilder::command('grep')
            ->addShortOption('a')
            ->addShortOption(
                'v',
                'enable the sandbox mode'
            );
    }

    private function getDatabaseCreateStatement(string $targetDatabase): string
    {
        $escapedTargetDatabase = '`' . str_replace('`', '``', $targetDatabase) . '`';
        return sprintf('CREATE DATABASE IF NOT EXISTS %s;USE %s;', $escapedTargetDatabase, $escapedTargetDatabase);
    }

    /**
     * @param string[] $excludes
     */
    private function getExcludeSqlPart(array $excludes): string
    {
        $result = '';
        $simpleExclude = [];
        foreach ($excludes as $exclude) {
            $stringLength = strlen($exclude);
            // can be replaced in php 8.0 with str_starts_with and str_end_with
            if ($stringLength >= 3 && str_starts_with($exclude, '/') && $exclude[$stringLength - 1] === '/') {
                $result .= " AND table_name NOT REGEXP '" . substr($exclude, 1, $stringLength - 2) . "'";
            } else {
                $simpleExclude[] = "'" . $exclude . "'";
            }
        }

        if ($simpleExclude) {
            $result .= ' AND table_name NOT IN(' . implode(',', $simpleExclude) . ')';
        }

        return $result;
    }

    /**
     * @param string[] $excludes
     */
    private function generateSqlQuery(string $database, array $excludes): string
    {
        $whereCondition = $this->getExcludeSqlPart($excludes);
        return <<<SQL
SET group_concat_max_len = 51200; SELECT GROUP_CONCAT(table_name separator ' ') FROM information_schema.tables WHERE table_schema='{$database}'{$whereCondition}
SQL;
    }
}
