<?php
declare(strict_types=1);

namespace PHPSu\Charlie;

use PHPSu\Alpha\GlobalConfig;
use PHPSu\Alpha\TheInterface as TheAlphaInterface;
use PHPSu\Beta\TheInterface as TheBetaInterface;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class Runner
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

    public function sync(string $form, string $to, string $currentHost, bool $dryRun): void
    {
        $alpha = new TheAlphaInterface();
        $commands = $alpha->getCommands($this->config, $form, $to, $currentHost);

        if ($dryRun) {
            $table = new Table($this->output);
            $table->setHeaders(['Name', 'Bash Command']);
            foreach ($commands as $commandName => $command) {
                $table->addRow([$commandName, $command]);
            }
            $table->render();
            return;
        }

        $beta = new TheBetaInterface();
        if ($this->output instanceof ConsoleOutputInterface) {
            $sectionTop = $this->output->section();
            $sectionMiddle = $this->output->section();
            $sectionMiddle->writeln(str_repeat('-', 20), OutputInterface::OUTPUT_RAW);
            $sectionBottom = $this->output->section();
        } else {
            $sectionTop = $this->output;
            $sectionBottom = $this->output;
        }
        $beta->execute($commands, $sectionTop, $sectionBottom);
    }
}
