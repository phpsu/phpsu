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
    /**
     * @return void
     */
    protected function configure()
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
        $source = $input->getArgument('source');
        if (!is_string($source)) {
            throw new \Exception('misconfigured source, source must be string');
        }
        $destination = $input->getArgument('destination');
        if (!is_string($destination)) {
            throw new \Exception('misconfigured destination, destination must be string');
        }
        $currentHost = $input->getOption('from');
        if (!is_string($currentHost)) {
            throw new \Exception('misconfigured currentHost, currentHost must be string');
        }

        $this->controller->sync(
            $output,
            $configuration,
            (new SyncOptions(StringHelper::findStringInArray($source, $instances) ?: $source))
                ->setDestination(StringHelper::findStringInArray($destination, $instances) ?: $destination)
                ->setCurrentHost($currentHost)
                ->setDryRun((bool)$input->getOption('dry-run'))
                ->setAll((bool)$input->getOption('all'))
                ->setNoFiles((bool)$input->getOption('no-fs'))
                ->setNoDatabases((bool)$input->getOption('no-db'))
        );
        return 0;
    }
}
