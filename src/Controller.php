<?php

declare(strict_types=1);

namespace PHPSu;

use PHPSu\Command\CommandGenerator;
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

    private CommandExecutor $executor;
    private CommandGenerator $commandGenerator;

    public function __construct(CommandGenerator $commandGenerator, CommandExecutor $commandExecutor = null)
    {
        $this->executor = $commandExecutor ?? new CommandExecutor();
        $this->commandGenerator = $commandGenerator;
    }

    public function ssh(OutputInterface $output, SshOptions $options): int
    {
        $sshCommand = $this->commandGenerator
            ->setVerbosity($output->getVerbosity())
            ->sshCommand($options->getDestination(), $options->getCurrentHost(), $options->getCommand());
        if ($options->isDryRun()) {
            $output->writeln((string)$sshCommand);
            return 0;
        }
        return $this->executor->passthru($sshCommand, $output);
    }

    public function mysql(OutputInterface $output, MysqlOptions $options): int
    {
        $mysqlCommand = $this->commandGenerator
            ->setVerbosity($output->getVerbosity())
            ->mysqlCommand(
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
        $commands = $this->commandGenerator
            ->setVerbosity($output->getVerbosity())
            ->syncCommands($options);

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
            $this->establishSshConnection($output, $options->getSource(), $options->isDryRun());
        }
        if ($options->getDestination() !== 'local') {
            $this->establishSshConnection($output, $options->getDestination(), $options->isDryRun());
        }
    }

    private function establishSshConnection(OutputInterface $output, string $source, bool $dryRun): void
    {
        $sshOptions = new SshOptions($source);
        $sshOptions->setDryRun($dryRun);
        $sshOptions->setCommand(
            ShellBuilder::command('echo')
                ->addArgument(sprintf('ssh connection to %s is working', $source))
        );
        $this->ssh($output, $sshOptions);
    }
}
