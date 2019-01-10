<?php
declare(strict_types=1);

namespace PHPSu\Main;

use PHPSu\Command\CommandGenerator;
use PHPSu\Config\GlobalConfig;
use PHPSu\Process\CommandExecutor;
use Symfony\Component\Console\Helper\Table;
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

    public function ssh(string $destination, string $currentHost): void
    {
        throw new \Exception('TODO Implement');
    }

    public function sync(string $form, string $to, string $currentHost, bool $dryRun): void
    {
        $alpha = new CommandGenerator();
        $commands = $alpha->syncCommands($this->config, $form, $to, $currentHost);

        if ($dryRun) {
            $table = new Table($this->output);
            $table->setHeaders(['Name', 'Bash Command']);
            foreach ($commands as $commandName => $command) {
                $table->addRow([$commandName, $command]);
            }
            $table->render();
            return;
        }

        $beta = new CommandExecutor();
        if ($this->output instanceof ConsoleOutputInterface) {
            $sectionTop = $this->output->section();
            $sectionMiddle = $this->output->section();
            $sectionMiddle->writeln(str_repeat('-', 20), OutputInterface::OUTPUT_RAW);
            $sectionBottom = $this->output->section();
        } else {
            $sectionTop = $this->output;
            $sectionBottom = $this->output;
        }
        $beta->executeParallel($commands, $sectionTop, $sectionBottom);
    }
}
