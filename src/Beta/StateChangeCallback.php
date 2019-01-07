<?php
declare(strict_types=1);

namespace PHPSu\Beta;

use Symfony\Component\Console\Output\ConsoleSectionOutput;
use Symfony\Component\Console\Output\OutputInterface;

final class StateChangeCallback
{
    /** @var OutputInterface */
    private $output;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function __invoke(Process $process, string $newState, ProcessManager $manager)
    {
        if ($this->output instanceof ConsoleSectionOutput) {
            $this->sectionCall($this->output, $manager);
        } else {
            $this->normalCall($process, $newState);
        }
    }

    private function sectionCall(ConsoleSectionOutput $sectionOutput, ProcessManager $manager): void
    {
        $lines = [];
        foreach ($manager->getProcesses() as $processId => $process) {
            $lines [] = $this->getMessage($manager->getState($processId), $process->getName());
        }
        $sectionOutput->overwrite(implode(PHP_EOL, $lines));
    }

    private function normalCall(Process $process, string $state): void
    {
        $this->output->writeln($this->getMessage($state, $process->getName()));
    }

    private function getMessage(string $state, string $name): string
    {
        switch ($state) {
            case Process::STATE_READY:
                $color = 'white';
                $statusSymbol = ' ';
                break;
            case Process::STATE_RUNNING:
                $color = 'yellow';
                $statusSymbol = '>';
                break;
            case Process::STATE_SUCCEEDED:
                $color = 'green';
                $statusSymbol = '✔';
                break;
            case Process::STATE_ERRORED:
                $color = 'red';
                $statusSymbol = '✘';
                break;
            default:
                throw new \LogicException('This should never happen (State not considered)');
        }
        return sprintf('<fg=%s>%s:</> %s', $color, $name, $statusSymbol);
    }
}
