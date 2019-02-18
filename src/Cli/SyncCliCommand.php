<?php
declare(strict_types=1);

namespace PHPSu\Cli;

use PHPSu\Config\ConfigurationLoaderInterface;
use PHPSu\ControllerInterface;
use PHPSu\Helper\StringHelper;
use PHPSu\SyncOptions;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class SyncCliCommand extends Command
{
    /** @var ConfigurationLoaderInterface */
    private $configurationLoader;
    /** @var ControllerInterface */
    private $controller;

    public function __construct(ConfigurationLoaderInterface $configurationLoader, ControllerInterface $controller)
    {
        parent::__construct();
        $this->configurationLoader = $configurationLoader;
        $this->controller = $controller;
    }

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
                ->setNoFiles($input->getOption('no-fs'))
                ->setNoDatabases($input->getOption('no-db'))
        );
        return 0;
    }
}
