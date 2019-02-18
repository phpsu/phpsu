<?php
declare(strict_types=1);

namespace PHPSu;

use PHPSu\Command\CommandGenerator;
use PHPSu\Config\GlobalConfig;
use PHPSu\Options\SshOptions;
use PHPSu\Options\SyncOptions;
use PHPSu\Process\CommandExecutor;
use PHPSu\Tools\EnvironmentUtility;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class Controller implements ControllerInterface
{
    public const PHPSU_ROOT_PATH = __DIR__ . '/../';

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

        $sectionTop = $output;
        $sectionBottom = $output;
        $sectionOutput = [];

        if ($output instanceof ConsoleOutputInterface) {
            $sectionTop = $this->getNewSection($sectionOutput, $output);
            $sectionMiddle = $this->getNewSection($sectionOutput, $output);
            $sectionMiddle->writeln(str_repeat('-', 20), OutputInterface::OUTPUT_RAW);
            $sectionBottom = $this->getNewSection($sectionOutput, $output);
        }
        (new CommandExecutor())->executeParallel($commands, $sectionTop, $sectionBottom);
    }

    /**
     * @deprecated the usage of symfony 3.x is discouraged. With the next version we will remove support
     * @param array $sectionOutputs
     * @param OutputInterface $output
     * @return Symfony\Component\Console\Output\ConsoleSectionOutput|Tools\ConsolePolyfill\ConsoleSectionOutput
     */
    private function getNewSection(array &$sectionOutputs, OutputInterface $output)
    {
        if (version_compare((new EnvironmentUtility())->getSymfonyProcessVersion(), '4.0.0', '>=')) {
            return $output->section();
        }
        return new Tools\ConsolePolyfill\ConsoleSectionOutput(
            $output->getStream(),
            $sectionOutputs,
            $output->getVerbosity(),
            $output->isDecorated(),
            $output->getFormatter()
        );
    }
}
