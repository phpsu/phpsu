<?php
declare(strict_types=1);

namespace PHPSu\Options;

final class SshOptions
{
    /** @var string */
    private $destination;
    /** @var string */
    private $currentHost = 'local';
    /** @var string */
    private $command = '';
    /** @var bool */
    private $dryRun = false;

    public function __construct(string $destination)
    {
        $this->destination = $destination;
    }

    public function getDestination(): string
    {
        return $this->destination;
    }

    public function setDestination(string $destination): SshOptions
    {
        $this->destination = $destination;
        return $this;
    }

    public function getCurrentHost(): string
    {
        return $this->currentHost;
    }

    public function setCurrentHost(string $currentHost): SshOptions
    {
        $this->currentHost = $currentHost;
        return $this;
    }

    public function getCommand(): string
    {
        return $this->command;
    }

    public function setCommand(string $command): SshOptions
    {
        $this->command = $command;
        return $this;
    }

    public function isDryRun(): bool
    {
        return $this->dryRun;
    }

    public function setDryRun(bool $dryRun): SshOptions
    {
        $this->dryRun = $dryRun;
        return $this;
    }
}
