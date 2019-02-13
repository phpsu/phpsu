<?php
declare(strict_types=1);

namespace PHPSu;

use PHPSu\Command\CommandGenerator;
use PHPSu\Config\GlobalConfig;
use PHPSu\Process\CommandExecutor;
use PHPSu\Tools\EnvironmentUtility;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;

if (!class_exists('ConsoleSectionOutput', false)) {
    if (version_compare((new EnvironmentUtility())->getSymfonyProcessVersion(), '4.0.0', 'gte')) {
        \class_alias(Symfony\Component\Console\Output\ConsoleSectionOutput::class, 'ConsoleSectionOutput');
    } else {
        \class_alias(PHPSu\Tools\ConsolePolyfill\ConsoleSectionOutput::class, 'ConsoleSectionOutput');
    }
}

use \ConsoleSectionOutput;

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
            if (method_exists($this->output, 'section')) {
                $sectionTop = $this->output->section();
                $sectionMiddle = $this->output->section();
                $sectionMiddle->writeln(str_repeat('-', 20), OutputInterface::OUTPUT_RAW);
                $sectionBottom = $this->output->section();
            } else {
                $sectionOutput = [];
                $sectionTop = $this->getNewSection($sectionOutput);
                $sectionMiddle = $this->getNewSection($sectionOutput);
                $sectionMiddle->writeln(str_repeat('-', 20), OutputInterface::OUTPUT_RAW);
                $sectionBottom = $this->getNewSection($sectionOutput);
            }
        } else {
            $sectionTop = $this->output;
            $sectionBottom = $this->output;
        }
        (new CommandExecutor())->executeParallel($commands, $sectionTop, $sectionBottom);
    }

    /**
     * @deprecated the usage of symfony 3.x is discouraged. With the next version we will remove support for that again
     * @param array $sectionOutputs
     * @return ConsoleSectionOutput
     */
    private function getNewSection(array &$sectionOutputs): ConsoleSectionOutput
    {
        return new ConsoleSectionOutput(
            $this->output->getStream(),
            $sectionOutputs,
            $this->output->getVerbosity(),
            $this->output->isDecorated(),
            $this->output->getFormatter()
        );
    }

}
