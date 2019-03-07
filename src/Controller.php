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
use Symfony\Component\Console\Output\ConsoleSectionOutput;
use Symfony\Component\Console\Output\OutputInterface;

final class Controller implements ControllerInterface
{
    const PHPSU_ROOT_PATH = __DIR__ . '/../';

    public function ssh(OutputInterface $output, GlobalConfig $config, SshOptions $options): int
    {
        $sshCommand = (new CommandGenerator($config, $output->getVerbosity()))->sshCommand($options->getDestination(), $options->getCurrentHost(), $options->getCommand());
        if ($options->isDryRun()) {
            $output->writeln($sshCommand);
            return 0;
        }
        return (new CommandExecutor())->passthru($sshCommand);
    }

    /**
     * @return void
     */
    public function sync(OutputInterface $output, GlobalConfig $config, SyncOptions $options)
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

        if ($output instanceof ConsoleOutputInterface) {
            $sectionOutput = [];
            $sectionTop = $this->getNewSection($sectionOutput, $output);
            $sectionMiddle = $this->getNewSection($sectionOutput, $output);
            $sectionMiddle->writeln(str_repeat('-', 20), OutputInterface::OUTPUT_RAW);
            $sectionBottom = $this->getNewSection($sectionOutput, $output);
        }
        (new CommandExecutor())->executeParallel($commands, $sectionTop, $sectionBottom);
    }

    /**
     * @param array $sectionOutputs
     * @param ConsoleOutputInterface $output
     * @return \Symfony\Component\Console\Output\ConsoleSectionOutput
     * @deprecated the usage of symfony 3.x is discouraged. With the next version we will remove support
     */
    private function getNewSection(array &$sectionOutputs, ConsoleOutputInterface $output): ConsoleSectionOutput
    {
        if (method_exists($output, 'section') && version_compare((new EnvironmentUtility())->getSymfonyProcessVersion(), '4.0.0', '>=')) {
            return $output->section();
        }
        return new ConsoleSectionOutput($output->getStream(), $sectionOutputs, $output->getVerbosity(), $output->isDecorated(), $output->getFormatter());
    }
}
