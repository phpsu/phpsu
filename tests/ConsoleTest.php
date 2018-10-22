<?php
namespace PHPSu\Tests;

class ConsoleTest extends \Codeception\Test\Unit
{
    /**
     * @var \Symfony\Component\Console\Tester\CommandTester
     */
    protected $commandTester;

    /**
     * @throws Exception
     */
    public function _before()
    {
        $kernel = new \PHPSu\CommandController();
        $application = $kernel->init()->phpsu->get(\Symfony\Component\Console\Application::class);
        $application->add(new \PHPSu\Console\SyncCommand);
        $command = $application->find('phpsu:sync');
        $this->commandTester = new \Symfony\Component\Console\Tester\CommandTester($command);
    }

    public function testSyncCommandExecution()
    {
        $this->commandTester->execute(['direction' => 'blablabla']);
        $this->assertEquals(0, $this->commandTester->getStatusCode());
    }

    public function testSyncCommandDirectionDeterminable()
    {
        $this->commandTester->execute(['direction' => 'blablabla']);
        $this->assertEquals(0, $this->commandTester->getStatusCode());
    }
}
