<?php

declare(strict_types=1);

namespace PHPSu\Options;

use PHPSu\ShellCommandBuilder\ShellBuilder;
use PHPSu\ShellCommandBuilder\ShellInterface;

/**
 * @internal
 */
final class SshOptions
{
    private string $destination;
    private string $currentHost = 'local';
    private ShellInterface $command;
    private bool $dryRun = false;

    public function __construct(string $destination)
    {
        $this->destination = $destination;
        $this->command = ShellBuilder::new();
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

    public function getCommand(): ShellInterface
    {
        return $this->command;
    }

    public function setCommand(ShellInterface $command): SshOptions
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
