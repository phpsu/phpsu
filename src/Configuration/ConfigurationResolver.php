<?php
declare(strict_types=1);

namespace PHPSu\Configuration;

use League\Container\Container;
use PHPSu\Configuration\ProcessedConfiguration\ProcessedConfigurationDto;
use PHPSu\Configuration\RawConfiguration\RawConfigurationDto;
use PHPSu\Configuration\RawConfiguration\RawDatabaseBag;
use PHPSu\Configuration\RawConfiguration\RawFilesystemBag;
use PHPSu\Configuration\RawConfiguration\RawHostDto;
use PHPSu\Core\ApplicationContext;

class ConfigurationResolver
{
    const DEFAULT_NAME = 'Default';
    const DEFAULT_CONSOLE_TYPE = 'local';
    const DEFAULT_DATABASE_TYPE = 'auto';
    const DEFAULT_FILESYSTEM_TYPE = 'directory';

    /**
     * @var Container
     */
    private $container;

    /**
     * @var ApplicationContext
     */
    private $context;

    public function __construct(Container $container, ApplicationContext $context)
    {
        $this->container = $container;
        $this->context = $context;
    }

    public function resolveRawConfigToProcessed(RawConfigurationDto $rawConfiguration): ProcessedConfigurationDto
    {
        $rawConfiguration = $this->addDefaultHosts($rawConfiguration);
        $rawConfiguration = $this->setDefaultsToRawConfig($rawConfiguration);
        $rawConfiguration = $this->overlyDefaultConfigToHosts($rawConfiguration);
        return $this->rawToProcessedClasses($rawConfiguration);
    }

    protected function rawToProcessedClasses(RawConfigurationDto $rawConfiguration): ProcessedConfigurationDto
    {
        $varsExported = var_export($rawConfiguration, true);
        $varsReWired = str_replace('PHPSu\Configuration\RawConfiguration\Raw', 'PHPSu\Configuration\ProcessedConfiguration\Processed', $varsExported);
        return $this->evalSandbox($varsReWired);
    }

    private function evalSandbox(string $exportedVar): ProcessedConfigurationDto
    {
        return eval('return ' . $exportedVar . ';');
    }

    protected function setDefaultsToRawConfig(RawConfigurationDto $rawConfiguration): RawConfigurationDto
    {
        foreach ($rawConfiguration->getHosts() as $host) {
            if ($host->getName() === '') {
                unset($rawConfiguration->getHosts()['']);
                $host->setName(self::DEFAULT_NAME);
                $rawConfiguration->getHosts()->offsetSet(self::DEFAULT_NAME, $host);
            }
            $console = $host->getConsole();
            if ($console->getName() === '') {
                $console->setName(self::DEFAULT_NAME);
            }
            if ($console->getType() === '') {
                $console->setType(self::DEFAULT_CONSOLE_TYPE);
            }
            $this->setDefaultToFilesystems($host->getFilesystems());
            $this->setDefaultToDatabases($host->getDatabases());
        }
        $this->setDefaultToFilesystems($rawConfiguration->getFilesystems());
        $this->setDefaultToDatabases($rawConfiguration->getDatabases());
        return $rawConfiguration;
    }

    protected function setDefaultToFilesystems(RawFilesystemBag $rawFilesystemBag)
    {
        foreach ($rawFilesystemBag as $filesystems) {
            if ($filesystems->getName() === '') {
                unset($rawFilesystemBag['']);
                $filesystems->setName(self::DEFAULT_NAME);
                $rawFilesystemBag->offsetSet(self::DEFAULT_NAME, $filesystems);
            }
            if ($filesystems->getType() === '') {
                $filesystems->setType(self::DEFAULT_FILESYSTEM_TYPE);
            }
        }
    }

    protected function setDefaultToDatabases(RawDatabaseBag $rawDatabaseBag)
    {
        foreach ($rawDatabaseBag as $database) {
            if ($database->getName() === '') {
                unset($rawDatabaseBag['']);
                $database->setName(self::DEFAULT_NAME);
                $rawDatabaseBag->offsetSet(self::DEFAULT_NAME, $database);
            }
            if ($database->getType() === '') {
                $database->setType(self::DEFAULT_DATABASE_TYPE);
            }
        }
    }

    protected function overlyDefaultConfigToHosts(RawConfigurationDto $rawConfiguration): RawConfigurationDto
    {
        foreach ($rawConfiguration->getHosts() as $host) {
            $this->overlayDatabasesOnHost($host, $rawConfiguration->getDatabases());
            $this->overlayFilesystemsOnHost($host, $rawConfiguration->getFilesystems());
        }
        return $rawConfiguration;
    }

    protected function overlayDatabasesOnHost(RawConfiguration\RawHostDto $host, RawDatabaseBag $databases)
    {
        foreach ($databases as $database) {
            if (!isset($host->getDatabases()[$database->getName()])) {
                $host->getDatabases()[] = $database;
                continue;
            }
            foreach ($database->getOptions() as $key => $value) {
                if (!isset($host->getDatabases()[$database->getName()]->getOptions()[$key])) {
                    $host->getDatabases()[$database->getName()]->getOptions()[$key] = $value;
                }
            }
        }
    }

    protected function overlayFilesystemsOnHost(RawConfiguration\RawHostDto $host, RawFilesystemBag $filesystems)
    {
        foreach ($filesystems as $filesystem) {
            if (!isset($host->getFilesystems()[$filesystem->getName()])) {
                $host->getFilesystems()[] = $filesystem;
                continue;
            }
            foreach ($filesystem->getOptions() as $key => $value) {
                if (!isset($host->getFilesystems()[$filesystem->getName()]->getOptions()[$key])) {
                    $host->getFilesystems()[$filesystem->getName()]->getOptions()[$key] = $value;
                }
            }
        }
    }

    protected function addDefaultHosts(RawConfigurationDto $rawConfiguration): RawConfigurationDto
    {
        if (!isset($rawConfiguration->getHosts()['local'])) {
            $rawConfiguration->getHosts()[] = new RawHostDto('local');
        }
        return $rawConfiguration;
    }
}
