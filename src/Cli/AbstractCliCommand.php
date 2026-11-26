<?php

declare(strict_types=1);

namespace PHPSu\Cli;

use PHPSu\Config\ConfigurationLoaderInterface;
use PHPSu\Config\GlobalConfig;
use PHPSu\Config\SshConfigFileLoader;
use PHPSu\ControllerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @internal
 */
abstract class AbstractCliCommand extends Command
{
    protected SshConfigFileLoader $sshConfigFileLoader;

    public function __construct(protected ConfigurationLoaderInterface $configurationLoader, protected ControllerInterface $controller, ?SshConfigFileLoader $sshConfigFileLoader = null)
    {
        $this->sshConfigFileLoader = $sshConfigFileLoader ?? new SshConfigFileLoader();
        parent::__construct();
    }

    protected function addRefreshSshConfigOption(): static
    {
        return $this->addOption('refresh-ssh-config', null, InputOption::VALUE_NONE, 'Download all ssh config files again, ignoring the cache.');
    }

    /**
     * returns the config with all ssh config files loaded
     */
    protected function getLoadedConfig(InputInterface $input, OutputInterface $output): GlobalConfig
    {
        $configuration = $this->configurationLoader->getConfig();
        $this->sshConfigFileLoader->loadAll($configuration, $output, (bool)$input->getOption('refresh-ssh-config'));
        return $configuration;
    }
}
