<?php
declare(strict_types=1);

namespace PHPSu\Cli;

use PHPSu\Helper\StringHelper;
use PHPSu\Options\SyncOptions;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class SyncCliCommand extends AbstractCliCommand
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
            ->addArgument('destination', InputArgument::OPTIONAL, 'The Destination AppInstance.', 'local');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $configuration = $this->configurationLoader->getConfig();
        $instances = $configuration->getAppInstanceNames();
        $source = $input->getArgument('source');
        $destination = $input->getArgument('destination');

        $this->controller->sync(
            $output,
            $configuration,
            (new SyncOptions(StringHelper::findStringInArray($source, $instances) ?: $source))
                ->setDestination(StringHelper::findStringInArray($destination, $instances) ?: $destination)
                ->setCurrentHost($input->getOption('from'))
                ->setDryRun($input->getOption('dry-run'))
                ->setAll($input->getOption('all'))
                ->setNoFiles($input->getOption('no-file'))
                ->setNoDatabases($input->getOption('no-db'))
        );
        return 0;
    }
}
