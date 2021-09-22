<?php

declare(strict_types=1);

namespace PHPSu\Process;

use LogicException;
use Symfony\Component\Process\Process as SymfonyProcess;

/**
 * @internal
 */
final class Process extends SymfonyProcess
{
    public const STATE_READY = 'ready';
    public const STATE_RUNNING = 'running';
    public const STATE_SUCCEEDED = 'succeeded';
    public const STATE_ERRORED = 'errored';

    private string $name = '';

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return self<mixed>
     */
    public function setName(string $name): Process
    {
        $this->name = $name;
        return $this;
    }

    public function getState(): string
    {
        switch ($this->getStatus()) {
            case self::STATUS_READY:
                return self::STATE_READY;
            case self::STATUS_STARTED:
                return self::STATE_RUNNING;
            case self::STATUS_TERMINATED:
                return $this->getExitCode() === 0 ? self::STATE_SUCCEEDED : self::STATE_ERRORED;
        }
        throw new LogicException('This should never happen');
    }
}
