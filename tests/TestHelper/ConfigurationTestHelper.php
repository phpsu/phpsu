<?php
declare(strict_types=1);

namespace PHPSu\Tests\TestHelper;

use PHPSu\Configuration\Dto\ConfigurationDto;
use PHPSu\Configuration\Dto\ConsoleDto;
use PHPSu\Configuration\Dto\DatabaseBag;
use PHPSu\Configuration\Dto\DatabaseDto;
use PHPSu\Configuration\Dto\FilesystemBag;
use PHPSu\Configuration\Dto\FilesystemDto;
use PHPSu\Configuration\Dto\HostBag;
use PHPSu\Configuration\Dto\HostDto;
use PHPSu\Configuration\Dto\OptionBag;
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

    public static function assertIfConfigurationDtoIsValid(ConfigurationDto $configuration)
    {
        $hosts = $configuration->getHosts();
        \PHPUnit\Framework\Assert::assertInstanceOf(HostBag::class, $hosts);
        foreach ($hosts as $host) {
            \PHPUnit\Framework\Assert::assertInstanceOf(HostDto::class, $host);

            $console = $host->getConsole();
            \PHPUnit\Framework\Assert::assertInstanceOf(ConsoleDto::class, $console);

            $options = $console->getOptions();
            \PHPUnit\Framework\Assert::assertInstanceOf(OptionBag::class, $options);

            $filesystems = $host->getFilesystems();
            \PHPUnit\Framework\Assert::assertInstanceOf(FilesystemBag::class, $filesystems);
            foreach ($filesystems as $filesystem) {
                \PHPUnit\Framework\Assert::assertInstanceOf(FilesystemDto::class, $filesystem);

                $options = $filesystem->getOptions();
                \PHPUnit\Framework\Assert::assertInstanceOf(OptionBag::class, $options);
            }

            $databases = $host->getDatabases();
            \PHPUnit\Framework\Assert::assertInstanceOf(DatabaseBag::class, $databases);
            foreach ($databases as $database) {
                \PHPUnit\Framework\Assert::assertInstanceOf(DatabaseDto::class, $database);

                $options = $database->getOptions();
                \PHPUnit\Framework\Assert::assertInstanceOf(OptionBag::class, $options);
            }
        }

        $filesystems = $configuration->getFilesystems();
        \PHPUnit\Framework\Assert::assertInstanceOf(FilesystemBag::class, $filesystems);
        foreach ($filesystems as $filesystem) {
            \PHPUnit\Framework\Assert::assertInstanceOf(FilesystemDto::class, $filesystem);
        }

        $databases = $configuration->getDatabases();
        \PHPUnit\Framework\Assert::assertInstanceOf(DatabaseBag::class, $databases);
        foreach ($databases as $database) {
            \PHPUnit\Framework\Assert::assertInstanceOf(DatabaseDto::class, $database);

            $options = $database->getOptions();
            \PHPUnit\Framework\Assert::assertInstanceOf(OptionBag::class, $options);
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
