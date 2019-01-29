<?php
declare(strict_types=1);

namespace PHPSu;

use PHPSu\Command\CommandGenerator;
use PHPSu\Config\GlobalConfig;
use PHPSu\Process\CommandExecutor;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class Controller
{
    /**
     * @var OutputInterface
     */
    private $output;
    /**
     * @var GlobalConfig
     */
    private $config;

    public function __construct(OutputInterface $output, GlobalConfig $config)
    {
        $this->output = $output;
        $this->config = $config;
    }

    public function ssh(string $destination, string $currentHost, string $command, bool $dryRun): int
    {
        $sshCommand = (new CommandGenerator($this->config))->sshCommand($destination, $currentHost, $command);
        if ($dryRun) {
            $this->output->writeln($sshCommand);
            return 0;
        }
        return (new CommandExecutor())->passthru($sshCommand, $this->output);
    }

    public function sync(string $form, string $to, string $currentHost, bool $dryRun, bool $all, bool $noFiles, bool $noDatabases): void
    {
        $commands = (new CommandGenerator($this->config))->syncCommands($form, $to, $currentHost, $all, $noFiles, $noDatabases);

        if ($dryRun) {
            foreach ($commands as $commandName => $command) {
                $this->output->writeln(sprintf('<info>%s</info>', $commandName));
                $this->output->writeln($command);
            }
            return;
        }

        if ($this->output instanceof ConsoleOutputInterface) {
            $sectionTop = $this->output->section();
            $sectionMiddle = $this->output->section();
            $sectionMiddle->writeln(str_repeat('-', 20), OutputInterface::OUTPUT_RAW);
            $sectionBottom = $this->output->section();
        } else {
            $sectionTop = $this->output;
            $sectionBottom = $this->output;
        }
        (new CommandExecutor())->executeParallel($commands, $sectionTop, $sectionBottom);
    }
}
