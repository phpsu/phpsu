<?php
declare(strict_types=1);

namespace PHPSu\Delta;

use PHPSu\Charlie\Runner;
use PHPSu\Foxtrot\ConfigurationLoader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class SyncCommand extends Command
{
    protected function configure(): void
    {
        $this->setName('sync')
            ->setDescription('Sync AppInstances')
            ->setHelp('Synchronizes Filesystem and/or Database from one AppInstance to another.')
            ->addOption('dry-run', 'd', InputOption::VALUE_NONE, 'Only show commands that would be run.')
            ->addOption('from', 'f', InputOption::VALUE_OPTIONAL, 'Only show commands that would be run.', '')
            ->addArgument('source', InputArgument::REQUIRED, 'The Source AppInstance.')
            ->addArgument('destination', InputArgument::REQUIRED, 'The Destination AppInstance.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $globalConfig = (new ConfigurationLoader())->getConfig();
        (new Runner($output))->runCli(
            $globalConfig,
            $input->getArgument('source'),
            $input->getArgument('destination'),
            $input->getOption('from'),
            $input->getOption('dry-run')
        );
        return 0;
    }
}
