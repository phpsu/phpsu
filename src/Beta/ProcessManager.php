<?php
declare(strict_types=1);

namespace PHPSu\Beta;

final class ProcessManager
{
    /** @var Process[] */
    private $processes = [];

    /** @var \Closure[] */
    private $callbacks = [];

    public function addOutputCallback(callable $callback): ProcessManager
    {
        $this->callbacks[] = \Closure::fromCallable($callback);
        return $this;
    }

    public function mustRun(): ProcessManager
    {
        $this->start();
        $this->wait();
        return $this;
    }

    public function start(): ProcessManager
    {
        foreach ($this->processes as $process) {
            $process->start(function (string $type, string $data) use ($process): void {
                $this->notifyOutputCallbacks($process, $type, $data);
            });
        }
        return $this;
    }

    public function addProcess(Process $process): ProcessManager
    {
        $this->processes[] = $process;
        return $this;
    }

    public function wait(): void
    {
        $count = 0;
        do {
            $running = false;
            foreach ($this->processes as $process) {
                if ($process->isRunning()) {
                    $running = true;
                    continue;
                }
            }
            usleep(min(100 * 1000, $count += 100));
        } while ($running);
        $this->validateProcesses();
    }

    private function notifyOutputCallbacks(Process $process, string $type, string $data): void
    {
        foreach ($this->callbacks as $callback) {
            $callback($process, $type, $data);
        }
    }

    public function getErrorOutputs(): array
    {
        $errors = [];
        foreach ($this->processes as $process) {
            if ($process->getErrorOutput()) {
                $errors[$process->getName()] = $process->getErrorOutput();
            }
        }
        return $errors;
    }

    private function validateProcesses(): void
    {
        $errors = [];
        foreach ($this->processes as $process) {
            if ($process->getExitCode() !== 0) {
                $errors[$process->getName()] = [
                    'code' => $process->getExitCode(),
                    'codeMessage' => $process->getExitCodeText(),
                    'out' => $process->getOutput(),
                    'err' => $process->getErrorOutput(),
                ];
            }
        }
        if ($errors) {
            throw new \Exception(sprintf('Error in Process%s %s', count($errors) > 1 ? 'es' : '', json_encode($errors)));
        }
    }
}
