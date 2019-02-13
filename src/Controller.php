<?php
declare(strict_types=1);

namespace PHPSu;

use PHPSu\Command\CommandGenerator;
use PHPSu\Config\GlobalConfig;
use PHPSu\Process\CommandExecutor;
use PHPSu\Tools\EnvironmentUtility;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;

// @codeCoverageIgnoreStart
if (!class_exists('ConsoleSectionOutput', false)) {
    if (version_compare((new EnvironmentUtility())->getSymfonyProcessVersion(), '4.0.0', 'gte')) {
        \class_alias(Symfony\Component\Console\Output\ConsoleSectionOutput::class, 'ConsoleSectionOutput');
    } else {
        \class_alias(Tools\ConsolePolyfill\ConsoleSectionOutput::class, 'ConsoleSectionOutput');
    }
}
// @codeCoverageIgnoreEnd

use \ConsoleSectionOutput;

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

        $sectionTop = null;
        $sectionBottom = null;

        if ($output instanceof ConsoleOutputInterface) {
            if (method_exists($output, 'section')) {
                $sectionTop = $output->section();
                $sectionMiddle = $output->section();
                $sectionMiddle->writeln(str_repeat('-', 20), OutputInterface::OUTPUT_RAW);
                $sectionBottom = $output->section();
            } else {
                $sectionOutput = [];
                $sectionTop = $this->getNewSection($sectionOutput, $output);
                $sectionMiddle = $this->getNewSection($sectionOutput, $output);
                $sectionMiddle->writeln(str_repeat('-', 20), OutputInterface::OUTPUT_RAW);
                $sectionBottom = $this->getNewSection($sectionOutput, $output);
            }
        }
        if ($sectionTop === null || $sectionBottom === null) {
            throw new \Exception('The output is not an instance of ConsoleOutputInterface therefore the sections are null');
        }
        (new CommandExecutor())->executeParallel($commands, $sectionTop, $sectionBottom);
    }

    /**
     * @codeCoverageIgnoreStart ignored for code coverage as this feature is going to be removed in the next version
     * @deprecated the usage of symfony 3.x is discouraged. With the next version we will remove support for that again
     * @param array $sectionOutputs
     * @param OutputInterface $output
     * @return ConsoleSectionOutput
     */
    private function getNewSection(array &$sectionOutputs, OutputInterface $output): ConsoleSectionOutput
    {
        return new ConsoleSectionOutput(
            $output->getStream(),
            $sectionOutputs,
            $output->getVerbosity(),
            $output->isDecorated(),
            $output->getFormatter()
        );
    }
    // @codeCoverageIgnoreEnd
}
