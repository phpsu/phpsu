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

    /**
     * Process constructor.
     * @param array|string $commandline
     * @param string|null $cwd
     * @param array|null $env
     * @param mixed|null $input
     * @param int $timeout
     * @param array $options
     */
    public function __construct($commandline, ?string $cwd = null, ?array $env = null, $input = null, $timeout = 60, array $options = array())
    {
        if (version_compare((new EnvironmentUtility())->getSymfonyProcessVersion(), '4.2.0', '<')) {
            if (\is_array($commandline)) {
                $commandline = $this->commandToString($commandline);
            }
            parent::__construct($commandline, $cwd, $env, $input, $timeout, $options);
            return;
        }
        parent::__construct($commandline, $cwd, $env, $input, $timeout, $options);
    }

    private function commandToString(array $commandline): string
    {
        $command = implode(' ', array_map(function ($argument) {
            if ('' === $argument || null === $argument) {
                return '""';
            }
            if ('\\' !== \DIRECTORY_SEPARATOR) {
                return "'".str_replace("'", "'\\''", $argument)."'";
            }
            if (false !== strpos($argument, "\0")) {
                $argument = str_replace("\0", '?', $argument);
            }
            if (!preg_match('/[\/()%!^"<>&|\s]/', $argument)) {
                return $argument;
            }
            $argument = preg_replace('/(\\\\+)$/', '$1$1', $argument);
            return '"'.str_replace(['"', '^', '%', '!', "\n"], ['""', '"^^"', '"^%"', '"^!"', '!LF!'], $argument).'"';
        }, $commandline));

        if ('\\' !== \DIRECTORY_SEPARATOR) {
            // exec is mandatory to deal with sending a signal to the process
            $command = 'exec '.$command;
        }

        return $command;
    }

    /**
     * This methods wraps the symfony behaviour of fromShellCommandline to make it possible to use phpsu for symfony 3 and 4 projects.
     *
     * {@inheritdoc}
     */
    public static function fromShellCommandline(string $command, string $cwd = null, array $env = null, $input = null, ?float $timeout = 60): self
    {
        if (version_compare((new EnvironmentUtility())->getSymfonyProcessVersion(), '4.0.0', '>')) {
            /** @noinspection PhpUndefinedMethodInspection Symfony version > 4.X */
            return parent::fromShellCommandline($command, $cwd, $env, $input, $timeout);
        }
        /** @noinspection PhpParamsInspection In symfony 3.2, passing $command as string was supported */
        return new static($command, $cwd, $env, $input, $timeout);
    }
}
