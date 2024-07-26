<?php

declare(strict_types=1);

namespace PHPSu\Options;

use PHPSu\ShellCommandBuilder\ShellInterface;

/**
 * @internal
 * Class MysqlOptions
 * @package PHPSu\Options
 */
final class MysqlOptions
{
    private string $appInstance = 'local';

    private string $currentHost = 'local';

    private ?string $command = null;

    private ?string $database = null;

    private bool $dryRun = false;

    public function getAppInstance(): string
    {
        return $this->appInstance;
    }

    public function setAppInstance(string $appInstance): self
    {
        $this->appInstance = $appInstance;
        return $this;
    }

    public function getCurrentHost(): string
    {
        return $this->currentHost;
    }

    public function setCurrentHost(string $currentHost): MysqlOptions
    {
        $this->currentHost = $currentHost;
        return $this;
    }

    public function getCommand(): ?string
    {
        return $this->command;
    }

    public function setCommand(?string $command): self
    {
        $this->command = $command;
        return $this;
    }

    public function isDryRun(): bool
    {
        return $this->dryRun;
    }

    public function setDryRun(bool $dryRun): self
    {
        $this->dryRun = $dryRun;
        return $this;
    }

    public function getDatabase(): ?string
    {
        return $this->database;
    }

    public function setDatabase(?string $database): self
    {
        $this->database = $database;
        return $this;
    }
}
