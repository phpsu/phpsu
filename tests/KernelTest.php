<?php

declare(strict_types=1);

namespace PHPSu\Tests;

use PHPSu\Cli\InfoCliCommand;
use PHPSu\Cli\MysqlCliCommand;
use PHPSu\Cli\SshCliCommand;
use PHPSu\Cli\SyncCliCommand;
use PHPSu\Config\ConfigurationLoader;
use PHPSu\Config\ConfigurationLoaderInterface;
use PHPSu\Config\GlobalConfig;
use PHPSu\Kernel;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;

final class KernelTest extends TestCase
{
    public function testPhpsuApplicationCommand(): void
    {
        $configurationLoader = new ConfigurationLoader();
        $reflection = new \ReflectionObject($configurationLoader);
        $property = $reflection->getProperty('config');
        $property->setAccessible(true);
        $property->setValue($configurationLoader, new GlobalConfig());

        Kernel::getContainer()->set(ConfigurationLoaderInterface::class, $configurationLoader);
        $app = Kernel::getContainer()->get(Application::class);
        $this->assertInstanceOf(SyncCliCommand::class, $app->get('sync'));
        $this->assertInstanceOf(SshCliCommand::class, $app->get('ssh'));
        $this->assertInstanceOf(MysqlCliCommand::class, $app->get('mysql'));
        $this->assertInstanceOf(InfoCliCommand::class, $app->get('info'));
    }
}
