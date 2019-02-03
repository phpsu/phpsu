<?php
declare(strict_types=1);

namespace PHPSu\Cli;

use PHPSu\Config\ConfigurationLoader;
use PHPSu\Controller;
use PHPSu\Helper\StringHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class SyncCliCommand extends Command
{
    protected function configure(): void
    {
        $this->setName('sync')
            ->setDescription('Sync AppInstances')
            ->setHelp('Synchronizes Filesystem and/or Database from one AppInstance to another.')
            ->addOption('dry-run', 'd', InputOption::VALUE_NONE, 'Only show commands that would be run.')
            ->addOption('all', 'a', InputOption::VALUE_NONE, 'Ignore all Excludes.')
            ->addOption('no-file', null, InputOption::VALUE_NONE, 'Do not sync Filesystems.')
            ->addOption('no-db', null, InputOption::VALUE_NONE, 'Do not sync Databases.')
            ->addOption('from', 'f', InputOption::VALUE_OPTIONAL, 'Only show commands that would be run.', '')
            ->addArgument('source', InputArgument::REQUIRED, 'The Source AppInstance.')
            ->addArgument('destination', InputArgument::REQUIRED, 'The Destination AppInstance.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $configuration = (new ConfigurationLoader())->getConfig();
        $instances = $configuration->getAppInstanceNames();
        $source = StringHelper::findStringInArray($input->getArgument('source'), $instances);
        $destination = StringHelper::findStringInArray($input->getArgument('destination'), $instances);

        (new Controller($output, $configuration))->sync(
            $source,
            $destination,
            $input->getOption('from'),
            $input->getOption('dry-run'),
            $input->getOption('all'),
            $input->getOption('no-file'),
            $input->getOption('no-db')
        );
        return 0;
    }
}
