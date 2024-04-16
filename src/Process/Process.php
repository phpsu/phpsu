<?php

declare(strict_types=1);

namespace PHPSu\Process;

use Generator;
use LogicException;
use PHPSu\Exceptions\CommandExecutionException;
use PHPSu\Tools\EnvironmentUtility;

/**
 * @internal
 */
final class Process extends \Symfony\Component\Process\Process
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
     * @return self<mixed>
     */
    public function setName(string $name): Process
    {
        $this->name = $name;
        return $this;
    }

    public function getState(): string
    {
        return match ($this->getStatus()) {
            self::STATUS_READY => self::STATE_READY,
            self::STATUS_STARTED => self::STATE_RUNNING,
            self::STATUS_TERMINATED => $this->getExitCode() === 0 ? self::STATE_SUCCEEDED : self::STATE_ERRORED,
            default => throw new LogicException('This should never happen'),
        };
    }
}
