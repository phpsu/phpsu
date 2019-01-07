<?php
declare(strict_types=1);

namespace PHPSu\Beta;

final class Process extends \Symfony\Component\Process\Process
{
    public const STATE_READY = 'ready';
    public const STATE_RUNNING = 'running';
    public const STATE_SUCCEEDED = 'succeeded';
    public const STATE_ERRORED = 'errored';

    /** @var string */
    private $name = '';

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): Process
    {
        $this->name = $name;
        return $this;
    }

    public function getState(): string
    {
        if ($this->getStatus() === self::STATUS_READY) {
            return self::STATE_READY;
        }
        if ($this->getStatus() === self::STATUS_STARTED) {
            return self::STATE_RUNNING;
        }
        if ($this->getStatus() === self::STATUS_TERMINATED) {
            return $this->getExitCode() === 0 ? self::STATE_SUCCEEDED : self::STATE_ERRORED;
        }
        throw new \LogicException('This should never happen');
    }
}
