<?php

declare(strict_types=1);

namespace PHPSu\Cli;

use PHPSu\Helper\StringHelper;
use PHPSu\Options\SyncOptions;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @internal
 */
final class SyncCliCommand extends AbstractCliCommand
{
    protected function configure(): void
    {
        $this->setName('sync')
            ->setDescription('Sync AppInstances')
            ->setHelp('Synchronizes Filesystem and/or Database from one AppInstance to another.')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Only show commands that would be run.')
            ->addOption('all', null, InputOption::VALUE_NONE, 'Ignore all Excludes.')
            ->addOption('no-fs', null, InputOption::VALUE_NONE, 'Do not sync Filesystems.')
            ->addOption('no-db', null, InputOption::VALUE_NONE, 'Do not sync Databases.')
            ->addOption('from', null, InputOption::VALUE_OPTIONAL, 'Only show commands that would be run.', '')
            ->addArgument('source', InputArgument::REQUIRED, 'The Source AppInstance.')
            ->addArgument('destination', InputArgument::OPTIONAL, 'The Destination AppInstance.', 'local');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $configuration = $this->configurationLoader->getConfig();
        $instances = $configuration->getAppInstanceNames();
        /** @var string $source */
        $source = $this->getArgument($input, 'source');
        /** @var string $destination */
        $destination = $this->getArgument($input, 'destination');
        /** @var string $currentHost */
        $currentHost = $this->getOption($input, 'from');

        $source = StringHelper::findStringInArray($source, $instances) ?: $source;
        $destination = StringHelper::findStringInArray($destination, $instances) ?: $destination;
        $options = (new SyncOptions($source))
            ->setDestination($destination)
            ->setCurrentHost($currentHost)
            ->setDryRun((bool)$input->getOption('dry-run'))
            ->setAll((bool)$input->getOption('all'))
            ->setNoFiles((bool)$input->getOption('no-fs'))
            ->setNoDatabases((bool)$input->getOption('no-db'));

        $this->controller->checkSshConnection($output, $configuration, $options);

        $this->controller->sync($output, $configuration, $options);

        return 0;
    }
}
