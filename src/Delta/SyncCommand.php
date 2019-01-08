<?php
declare(strict_types=1);

namespace PHPSu\Delta;

use PHPSu\Console\AbstractCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class SyncCommand extends AbstractCommand
{
    protected function configure(): void
    {
        $this->setName('sync')
            ->setDescription('Sync AppInstances')
            ->setHelp('Synchronizes Filesystem and/or Database from one AppInstance to another.')
            ->addOption('dry-run', 'd', InputOption::VALUE_NONE, 'Only show commands that would be run.')
            ->addOption('from', 'f', InputOption::VALUE_OPTIONAL, 'Only show commands that would be run.', 'local')
            ->addArgument('source', InputArgument::OPTIONAL, 'The Source AppInstance.', 'production')
            ->addArgument('destination', InputArgument::OPTIONAL, 'The Destination AppInstance.', 'local');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln(json_encode($input->getArguments(), JSON_PRETTY_PRINT));
        return 0;
    }
}
