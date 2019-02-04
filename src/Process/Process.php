<?php
declare(strict_types=1);

namespace PHPSu\Process;

use PHPSu\Tools\EnvironmentUtility;

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
        switch ($this->getStatus()) {
            case self::STATUS_READY:
                return self::STATE_READY;
            case self::STATUS_STARTED:
                return self::STATE_RUNNING;
            case self::STATUS_TERMINATED:
                return $this->getExitCode() === 0 ? self::STATE_SUCCEEDED : self::STATE_ERRORED;
        }
        throw new \LogicException('This should never happen');
    }

    public static function fromShellCommandline(string $command, string $cwd = null, array $env = null, $input = null, ?float $timeout = 60)
    {
        if (version_compare((new EnvironmentUtility())->getSymfonyProcessVersion(), '4.0.0', '>')) {
            return parent::fromShellCommandline($command, $cwd, $env, $input, $timeout);
        }
        /** @noinspection PhpParamsInspection In symfony 3.2, passing $command as string was supported */
        return new \Symfony\Component\Process\Process($command, $cwd, $env, $input, $timeout);
    }
}
