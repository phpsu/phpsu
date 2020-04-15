<?php

declare(strict_types=1);

namespace PHPSu\Tests\Cli;

use PHPSu\Cli\SshCliCommand;
use PHPSu\Config\ConfigurationLoaderInterface;
use PHPSu\Config\GlobalConfig;
use PHPSu\Controller;
use PHPSu\ControllerInterface;
use PHPSu\Options\SshOptions;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;

class SshCliCommandTest extends TestCase
{

    public function testSshCliCommandDryRun()
    {
        $mockConfigurationLoader = $this->createMockConfigurationLoader($this->createConfig());

        $command = new SshCliCommand($mockConfigurationLoader, new Controller());
        $command->setHelperSet(new HelperSet([
            new QuestionHelper(),
        ]));

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'destination' => 'p',
            '--dry-run' => true,
        ]);

        $output = $commandTester->getDisplay();
        $this->assertSame("ssh -F '.phpsu/config/ssh_config' 'us' -t 'cd '\''/var/www/'\''; bash --login'\n", $output);
        $this->assertSame(0, $commandTester->getStatusCode());
    }

    public function testSshCliCommandDryRunInteractive()
    {
        $mockConfigurationLoader = $this->createMockConfigurationLoader($this->createConfig());

        $command = new SshCliCommand($mockConfigurationLoader, new Controller());
        $command->setHelperSet(new HelperSet([
            new QuestionHelper(),
        ]));

        $commandTester = new CommandTester($command);
        $commandTester->setInputs(['production']);
        $commandTester->execute([
            '--dry-run' => true,
        ]);

        $output = $commandTester->getDisplay();
        $this->assertContains('Please select one of the AppInstances', $output);
        $this->assertContains('You selected: production', $output);
        $this->assertContains("ssh -F '.phpsu/config/ssh_config' 'us' -t 'cd '\''/var/www/'\''; bash --login'\n", $output);
        $this->assertSame(0, $commandTester->getStatusCode());
    }

    public function testSshCliCommandExecute()
    {
        $globalConfig = $this->createConfig();
        $mockConfigurationLoader = $this->createMockConfigurationLoader($globalConfig);

        /** @var MockObject|ControllerInterface $mockController */
        $mockController = $this->createMock(ControllerInterface::class);
        $mockController->expects($this->once())
            ->method('ssh')
            ->with(
                $this->isInstanceOf(OutputInterface::class),
                $this->equalTo($globalConfig),
                $this->equalTo(new SshOptions('production'))
            )
            ->willReturn(208);

        $command = new SshCliCommand($mockConfigurationLoader, $mockController);
        $command->setHelperSet(new HelperSet([
            new QuestionHelper(),
        ]));

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'destination' => 'p'
        ]);

        $output = $commandTester->getDisplay();
        $this->assertSame('', $output);
        $this->assertSame(208, $commandTester->getStatusCode());
    }

    public function testSshCliCommandExecuteWithNoAppInstancesConfigured()
    {
        $globalConfig = $this->createConfigNoAppInstance();
        $mockConfigurationLoader = $this->createMockConfigurationLoader($globalConfig);

        /** @var MockObject|ControllerInterface $mockController */
        $mockController = $this->createMock(ControllerInterface::class);

        $command = new SshCliCommand($mockConfigurationLoader, $mockController);
        $command->setHelperSet(new HelperSet([new QuestionHelper()]));
        $commandTester = new CommandTester($command);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('You need to define at least one AppInstance besides local');
        $commandTester->execute(['destination' => 'p']);
    }

    /**
     * @return GlobalConfig
     */
    private function createConfig(): GlobalConfig
    {
        $globalConfig = new GlobalConfig();
        $globalConfig->addSshConnection('us', 'ssh://user@us');
        $globalConfig->addAppInstance('production', 'us', '/var/www/');
        return $globalConfig;
    }

    /**
     * @return GlobalConfig
     */
    private function createConfigNoAppInstance(): GlobalConfig
    {
        $globalConfig = new GlobalConfig();
        $globalConfig->addSshConnection('us', 'ssh://user@us');
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
