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

    public function ssh(OutputInterface $output, GlobalConfig $config, string $destination, string $currentHost, string $command, bool $dryRun): int
    {
        $sshCommand = (new CommandGenerator($config))->sshCommand($destination, $currentHost, $command);
        if ($dryRun) {
            $output->writeln($sshCommand);
            return 0;
        }
        return (new CommandExecutor())->passthru($sshCommand, $output);
    }

    public function sync(OutputInterface $output, GlobalConfig $config, string $form, string $to, string $currentHost, bool $dryRun, bool $all, bool $noFiles, bool $noDatabases): void
    {
        $commands = (new CommandGenerator($config))->syncCommands($form, $to, $currentHost, $all, $noFiles, $noDatabases);

        if ($dryRun) {
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
