<?php
declare(strict_types=1);

namespace PHPSu\Process;

use Symfony\Component\Console\Output\ConsoleSectionOutput;
use Symfony\Component\Console\Output\OutputInterface;

final class StateChangeCallback
{
    /** @var OutputInterface */
    private $output;

    /** @var Spinner[] */
    private $processSpinners = [];

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function __invoke(int $processId, Process $process, string $newState, ProcessManager $manager)
    {
        if ($this->output instanceof ConsoleSectionOutput) {
            $this->sectionCall($this->output, $manager);
        } else {
            $this->normalCall($processId, $process, $newState);
        }
    }

    private function sectionCall(ConsoleSectionOutput $sectionOutput, ProcessManager $manager): void
    {
        $lines = [];
        foreach ($manager->getProcesses() as $processId => $process) {
            $lines[] = $this->getMessage($processId, $manager->getState($processId), $process->getName());
        }
        $sectionOutput->overwrite(implode(PHP_EOL, $lines));
    }

    private function normalCall(int $processId, Process $process, string $state): void
    {
        $this->output->writeln($this->getMessage($processId, $state, $process->getName()));
    }

    private function getMessage(int $processId, string $state, string $name): string
    {
        switch ($state) {
            case Process::STATE_READY:
                $color = 'white';
                $statusSymbol = ' ';
                break;
            case Process::STATE_RUNNING:
                $color = 'yellow';
                $statusSymbol = $this->getSpinner($processId)->spin();
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

    private function getSpinner(int $processId): Spinner
    {
        if (!isset($this->processSpinners[$processId])) {
            $this->processSpinners[$processId] = new Spinner();
        }
        return $this->processSpinners[$processId];
    }

    public function getTickCallback(): callable
    {
        $lastTick = microtime(true);
        return function (ProcessManager $manager) use (&$lastTick) {
            $currentTick = microtime(true);
            if ($currentTick - $lastTick < 0.1) {
                return;
            }
            $lastTick = $currentTick;
            if ($this->output instanceof ConsoleSectionOutput) {
                $this->sectionCall($this->output, $manager);
            }
        };
    }
}
