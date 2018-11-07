<?php
namespace PHPSu\Tests;

use PHPSu\CommandController;

class ConsoleTest extends \Codeception\Test\Unit
{
    /**
     * @var \Symfony\Component\Console\Tester\CommandTester
     */
    protected $commandTester;

    /**
     * @throws \Exception
     */
    public function _before()
    {
        $kernel = new CommandController();
        $application = $kernel->getDependency(\Symfony\Component\Console\Application::class);
        $application->add(new \PHPSu\Console\SyncCommand);
        $command = $application->find('sync');
        $this->commandTester = new \Symfony\Component\Console\Tester\CommandTester($command);
    }

    public function testSuccessfulSyncCommandExecution(): void
    {
        foreach ($this->getCommandExample() as $result) {
            $this->commandTester->execute(['direction' => $result['direction']]);
            $this->assertEquals($this->getSyncCommandResultMessage(...$result['result']), trim($this->commandTester->getDisplay(true)));
        }
    }

    private function getCommandExample(): ?\Generator
    {
        yield from [
            [
                'direction' => ['blabl<-abla'],
                'result' => ['abla', 'blabl']
            ],
            [
                'direction' => ['blabl=:abla'],
                'result' => ['abla', 'blabl']
            ],
            [
                'direction' => ['blabl-:abla'],
                'result' => ['abla', 'blabl']
            ],
            [
                'direction' => ['blabl←abla'],
                'result' => ['abla', 'blabl']
            ],
            [
                'direction' => ['blabl->abla'],
                'result' => ['blabl', 'abla']
            ],
            [
                'direction' => ['blabl:=abla'],
                'result' => ['blabl', 'abla']
            ],
            [
                'direction' => ['blabl:-abla'],
                'result' => ['blabl', 'abla']
            ],
            [
                'direction' => ['blabl→abla'],
                'result' => ['blabl', 'abla']
            ],
            [
                'direction' => [['blabl','to','abla']],
                'result' => ['blabl', 'abla']
            ],
            [
                'direction' => null,
                'result' => ['blabl', 'abla']
            ],
        ];
    }

    private function getSyncCommandResultMessage(string $from, string $to): string
    {
        return sprintf('PHPSu is going to synchronize from %s to %s', $from, $to);
    }
}
