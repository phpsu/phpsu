<?php

declare(strict_types=1);

namespace PHPSu;

use PHPSu\Command\CommandGenerator;
use PHPSu\Config\GlobalConfig;
use PHPSu\Options\SshOptions;
use PHPSu\Options\SyncOptions;
use PHPSu\Options\MysqlOptions;
use PHPSu\Process\CommandExecutor;
use PHPSu\ShellCommandBuilder\ShellBuilder;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @internal
 */
final class Controller implements ControllerInterface
{
    public const PHPSU_ROOT_PATH = __DIR__ . '/../';

    /** @var CommandExecutor */
    private $executor;
    /** @var GlobalConfig */
    private $config;

    public function __construct(GlobalConfig $config, CommandExecutor $commandExecutor = null)
    {
        $this->config = $config;
        $this->executor = $commandExecutor ?? new CommandExecutor();
    }

    public function ssh(OutputInterface $output, SshOptions $options): int
    {
        $sshCommand = (new CommandGenerator($this->config, $output->getVerbosity()))->sshCommand($options->getDestination(), $options->getCurrentHost(), $options->getCommand());
        if ($options->isDryRun()) {
            $output->writeln((string)$sshCommand);
            return 0;
        }
        return $this->executor->passthru($sshCommand, $output);
    }

    public function mysql(OutputInterface $output, MysqlOptions $options): int
    {
        $mysqlCommand = (new CommandGenerator($this->config, $output->getVerbosity()))->mysqlCommand(
            $options->getAppInstance(),
            $options->getDatabase(),
            $options->getCommand()
        );
        if ($options->isDryRun()) {
            $output->writeln((string)$mysqlCommand);
            return 0;
        }
        return $this->executor->passthru($mysqlCommand, $output);
    }

    public function sync(OutputInterface $output, SyncOptions $options): void
    {
        $commands = (new CommandGenerator($this->config, $output->getVerbosity()))->syncCommands($options);

        if ($options->isDryRun()) {
            foreach ($commands as $commandName => $command) {
                $output->writeln(sprintf('<info>%s</info>', $commandName));
                $output->writeln($command);
            }
            return;
        }

        $sectionTop = $output;
        $sectionBottom = $output;

        if ($output instanceof ConsoleOutputInterface) {
            $sectionTop = $output->section();
            $sectionMiddle = $output->section();
            $sectionMiddle->writeln(str_repeat('-', 20), OutputInterface::OUTPUT_RAW);
            $sectionBottom = $output->section();
        }
        $this->executor->executeParallel($commands, $sectionTop, $sectionBottom);
    }

    public function checkSshConnection(OutputInterface $output, SyncOptions $options): void
    {
        if ($options->getSource() !== 'local') {
            $sshOptionSource = new SshOptions($options->getSource());
            $sshOptionSource->setDryRun($options->isDryRun());
            $command = ShellBuilder::command('echo')
                ->addArgument(sprintf('ssh connection to %s is working', $sshOptionSource->getDestination()));
            $sshOptionSource->setCommand($command);
            $this->ssh($output, $sshOptionSource);
        }
        if ($options->getDestination() !== 'local') {
            $sshOptionDestination = new SshOptions($options->getDestination());
            $sshOptionDestination->setDryRun($options->isDryRun());
            $command = ShellBuilder::command('echo')
                ->addArgument(sprintf('ssh connection to %s is working', $sshOptionDestination->getDestination()));
            $sshOptionDestination->setCommand($command);
            $this->ssh($output, $sshOptionDestination);
        }
    }
}
