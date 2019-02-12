<?php
declare(strict_types=1);

namespace PHPSu;

use PHPSu\Command\CommandGenerator;
use PHPSu\Config\GlobalConfig;
use PHPSu\Process\CommandExecutor;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class Controller implements ControllerInterface
{

    public function ssh(OutputInterface $output, GlobalConfig $config, SshOptions $options): int
    {
        $sshCommand = (new CommandGenerator($config, $output->getVerbosity()))->sshCommand($options->getDestination(), $options->getCurrentHost(), $options->getCommand());
        if ($options->isDryRun()) {
            $output->writeln($sshCommand);
            return 0;
        }
        return (new CommandExecutor())->passthru($sshCommand, $output);
    }

    public function sync(OutputInterface $output, GlobalConfig $config, SyncOptions $options): void
    {
        $commands = (new CommandGenerator($config, $output->getVerbosity()))->syncCommands($options);

        if ($options->isDryRun()) {
            foreach ($commands as $commandName => $command) {
                $output->writeln(sprintf('<info>%s</info>', $commandName));
                $output->writeln($command);
            }
            return;
        }

        if ($output instanceof ConsoleOutputInterface) {
            $sectionTop = $output->section();
            $sectionMiddle = $output->section();
            $sectionMiddle->writeln(str_repeat('-', 20), OutputInterface::OUTPUT_RAW);
            $sectionBottom = $output->section();
        } else {
            $sectionTop = $output;
            $sectionBottom = $output;
        }
        (new CommandExecutor())->executeParallel($commands, $sectionTop, $sectionBottom);
    }
}
