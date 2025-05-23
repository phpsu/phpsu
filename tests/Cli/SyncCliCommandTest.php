<?php

declare(strict_types=1);

namespace PHPSu\Tests\Cli;

use PHPSu\Cli\SyncCliCommand;
use PHPSu\Config\ConfigurationLoaderInterface;
use PHPSu\Config\GlobalConfig;
use PHPSu\Controller;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Tester\CommandTester;

class SyncCliCommandTest extends TestCase
{
    public function testSyncCliCommandExecute(): void
    {
        $mockConfigurationLoader = $this->createMockConfigurationLoader($this->createConfig());
        $command = new SyncCliCommand($mockConfigurationLoader, new Controller());
        $command->setHelperSet(new HelperSet([
            new QuestionHelper(),
        ]));

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'source' => 'p',
            'destination' => 'l',
            '--dry-run' => true,
        ]);

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString("ssh -F '.phpsu/config/ssh_config' 'us' -t 'cd '\''/var/www/'\'' ; echo '\''ssh connection to production is working'\'''\n", $output);
        $this->assertStringContainsString("filesystem:storage\n", $output);
        $this->assertStringContainsString("rsync -az -e 'ssh -F '\''.phpsu/config/ssh_config'\''' 'us:/var/www/var/storage/' './var/storage/'\n", $output);
        $this->assertSame(0, $commandTester->getStatusCode());
    }

    public function testSyncCliCommandExecuteReversed(): void
    {
        $mockConfigurationLoader = $this->createMockConfigurationLoader($this->createConfig());
        $command = new SyncCliCommand($mockConfigurationLoader, new Controller());
        $command->setHelperSet(new HelperSet([
            new QuestionHelper(),
        ]));

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'source' => 'l',
            'destination' => 'p',
            '--dry-run' => true,
        ]);

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString("ssh -F '.phpsu/config/ssh_config' 'us' -t 'cd '\''/var/www/'\'' ; echo '\''ssh connection to production is working'\'''\n", $output);
        $this->assertStringContainsString("filesystem:storage\n", $output);
        $this->assertStringContainsString("rsync -az -e 'ssh -F '\''.phpsu/config/ssh_config'\''' './var/storage/' 'us:/var/www/var/storage/'\n", $output);
        $this->assertSame(0, $commandTester->getStatusCode());
    }

    private function createConfig(): GlobalConfig
    {
        $globalConfig = new GlobalConfig();
        $globalConfig->addFilesystem('storage', 'var/storage');
        $globalConfig->addSshConnection('us', 'ssh://user@us');
        $globalConfig->addAppInstance('production', 'us', '/var/www/');
        $globalConfig->addAppInstance('local');
        return $globalConfig;
    }

    private function createMockConfigurationLoader(GlobalConfig $config): ConfigurationLoaderInterface&MockObject
    {
        $mockConfigurationLoader = $this->createMock(ConfigurationLoaderInterface::class);
        $mockConfigurationLoader->expects($this->atLeastOnce())
            ->method('getConfig')
            ->willReturn($config);
        return $mockConfigurationLoader;
    }
}
