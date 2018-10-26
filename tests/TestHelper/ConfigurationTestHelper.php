<?php
declare(strict_types=1);

namespace PHPSu\Tests\TestHelper;

use PHPSu\Configuration\ProcessedConfiguration\ProcessedConfigurationDto;
use PHPSu\Configuration\ProcessedConfiguration\ProcessedConsoleDto;
use PHPSu\Configuration\ProcessedConfiguration\ProcessedDatabaseBag;
use PHPSu\Configuration\ProcessedConfiguration\ProcessedDatabaseDto;
use PHPSu\Configuration\ProcessedConfiguration\ProcessedFilesystemBag;
use PHPSu\Configuration\ProcessedConfiguration\ProcessedFilesystemDto;
use PHPSu\Configuration\ProcessedConfiguration\ProcessedHostBag;
use PHPSu\Configuration\ProcessedConfiguration\ProcessedHostDto;
use PHPSu\Configuration\ProcessedConfiguration\ProcessedOptionBag;
use PHPSu\Configuration\RawConfiguration\RawConfigurationDto;
use PHPSu\Configuration\RawConfiguration\RawConsoleDto;
use PHPSu\Configuration\RawConfiguration\RawDatabaseBag;
use PHPSu\Configuration\RawConfiguration\RawDatabaseDto;
use PHPSu\Configuration\RawConfiguration\RawFilesystemBag;
use PHPSu\Configuration\RawConfiguration\RawFilesystemDto;
use PHPSu\Configuration\RawConfiguration\RawHostBag;
use PHPSu\Configuration\RawConfiguration\RawHostDto;
use PHPSu\Configuration\RawConfiguration\RawOptionBag;

final class ConfigurationTestHelper
{
    private function __construct()
    {
    }

    public static function assertIfProcessedConfigurationDtoIsValid(ProcessedConfigurationDto $configuration)
    {
        $hosts = $configuration->getHosts();
        \PHPUnit\Framework\Assert::assertInstanceOf(ProcessedHostBag::class, $hosts);
        foreach ($hosts as $host) {
            \PHPUnit\Framework\Assert::assertInstanceOf(ProcessedHostDto::class, $host);

            $console = $host->getConsole();
            \PHPUnit\Framework\Assert::assertInstanceOf(ProcessedConsoleDto::class, $console);

            $options = $console->getOptions();
            \PHPUnit\Framework\Assert::assertInstanceOf(ProcessedOptionBag::class, $options);

            $filesystems = $host->getFilesystems();
            \PHPUnit\Framework\Assert::assertInstanceOf(ProcessedFilesystemBag::class, $filesystems);
            foreach ($filesystems as $filesystem) {
                \PHPUnit\Framework\Assert::assertInstanceOf(ProcessedFilesystemDto::class, $filesystem);

                $options = $filesystem->getOptions();
                \PHPUnit\Framework\Assert::assertInstanceOf(ProcessedOptionBag::class, $options);
            }

            $databases = $host->getDatabases();
            \PHPUnit\Framework\Assert::assertInstanceOf(ProcessedDatabaseBag::class, $databases);
            foreach ($databases as $database) {
                \PHPUnit\Framework\Assert::assertInstanceOf(ProcessedDatabaseDto::class, $database);

                $options = $database->getOptions();
                \PHPUnit\Framework\Assert::assertInstanceOf(ProcessedOptionBag::class, $options);
            }
        }

        $filesystems = $configuration->getFilesystems();
        \PHPUnit\Framework\Assert::assertInstanceOf(ProcessedFilesystemBag::class, $filesystems);
        foreach ($filesystems as $filesystem) {
            \PHPUnit\Framework\Assert::assertInstanceOf(ProcessedFilesystemDto::class, $filesystem);
        }

        $databases = $configuration->getDatabases();
        \PHPUnit\Framework\Assert::assertInstanceOf(ProcessedDatabaseBag::class, $databases);
        foreach ($databases as $database) {
            \PHPUnit\Framework\Assert::assertInstanceOf(ProcessedDatabaseDto::class, $database);

            $options = $database->getOptions();
            \PHPUnit\Framework\Assert::assertInstanceOf(ProcessedOptionBag::class, $options);
        }
    }

    public static function assertIfRawConfigurationDtoIsValid(RawConfigurationDto $configuration)
    {
        $hosts = $configuration->getHosts();
        \PHPUnit\Framework\Assert::assertInstanceOf(RawHostBag::class, $hosts);
        foreach ($hosts as $host) {
            \PHPUnit\Framework\Assert::assertInstanceOf(RawHostDto::class, $host);

            $console = $host->getConsole();
            \PHPUnit\Framework\Assert::assertInstanceOf(RawConsoleDto::class, $console);

            $options = $console->getOptions();
            \PHPUnit\Framework\Assert::assertInstanceOf(RawOptionBag::class, $options);

            $filesystems = $host->getFilesystems();
            \PHPUnit\Framework\Assert::assertInstanceOf(RawFilesystemBag::class, $filesystems);
            foreach ($filesystems as $filesystem) {
                \PHPUnit\Framework\Assert::assertInstanceOf(RawFilesystemDto::class, $filesystem);

                $options = $filesystem->getOptions();
                \PHPUnit\Framework\Assert::assertInstanceOf(RawOptionBag::class, $options);
            }

            $databases = $host->getDatabases();
            \PHPUnit\Framework\Assert::assertInstanceOf(RawDatabaseBag::class, $databases);
            foreach ($databases as $database) {
                \PHPUnit\Framework\Assert::assertInstanceOf(RawDatabaseDto::class, $database);

                $options = $database->getOptions();
                \PHPUnit\Framework\Assert::assertInstanceOf(RawOptionBag::class, $options);
            }
        }

        $filesystems = $configuration->getFilesystems();
        \PHPUnit\Framework\Assert::assertInstanceOf(RawFilesystemBag::class, $filesystems);
        foreach ($filesystems as $filesystem) {
            \PHPUnit\Framework\Assert::assertInstanceOf(RawFilesystemDto::class, $filesystem);
        }

        $databases = $configuration->getDatabases();
        \PHPUnit\Framework\Assert::assertInstanceOf(RawDatabaseBag::class, $databases);
        foreach ($databases as $database) {
            \PHPUnit\Framework\Assert::assertInstanceOf(RawDatabaseDto::class, $database);

            $options = $database->getOptions();
            \PHPUnit\Framework\Assert::assertInstanceOf(RawOptionBag::class, $options);
        }
    }
}
