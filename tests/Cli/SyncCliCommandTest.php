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

    public function testSyncCliCommandExecute()
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
        $this->assertContains("filesystem:storage\n", $output);
        $this->assertContains("rsync -az -e 'ssh -F '\''.phpsu/config/ssh_config'\''' 'us:/var/www/var/storage/' './var/storage/'\n", $output);
        $this->assertSame(0, $commandTester->getStatusCode());
    }

    /**
     * @return GlobalConfig
     */
    private function createConfig(): GlobalConfig
    {
        $globalConfig = new GlobalConfig();
        $globalConfig->addFilesystem('storage', 'var/storage');
        $globalConfig->addSshConnection('us', 'ssh://user@us');
        $globalConfig->addAppInstance('production', 'us', '/var/www/');
        $globalConfig->addAppInstance('local');
        return $globalConfig;
    }

    /**
     * @param GlobalConfig $config
     * @return ConfigurationLoaderInterface|MockObject
     */
    private function createMockConfigurationLoader(GlobalConfig $config)
    {
        /** @var MockObject|ConfigurationLoaderInterface $mockConfigurationLoader */
        $mockConfigurationLoader = $this->createMock(ConfigurationLoaderInterface::class);
        $mockConfigurationLoader->expects($this->atLeastOnce())
            ->method('getConfig')
            ->willReturn($config);
        return $mockConfigurationLoader;
    }
}
