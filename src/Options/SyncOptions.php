<?php

declare(strict_types=1);

namespace PHPSu\Options;

final class SyncOptions
{
    /** @var string */
    private $source;
    /** @var string */
    private $destination = 'local';
    /** @var string */
    private $currentHost = 'local';
    /** @var bool */
    private $dryRun = false;
    /** @var bool */
    private $all = false;
    /** @var bool */
    private $noFiles = false;
    /** @var bool */
    private $noDatabases = false;

    public function __construct(string $source)
    {
        $this->source = $source;
    }

    public function getSource(): string
    {
        return $this->source;
    }

    public function setSource(string $source): SyncOptions
    {
        $this->source = $source;
        return $this;
    }

    public function getDestination(): string
    {
        return $this->destination;
    }

    public function setDestination(string $destination): SyncOptions
    {
        $this->destination = $destination;
        return $this;
    }

    public function getCurrentHost(): string
    {
        return $this->currentHost;
    }

    public function setCurrentHost(string $currentHost): SyncOptions
    {
        $this->currentHost = $currentHost;
        return $this;
    }

    public function isDryRun(): bool
    {
        return $this->dryRun;
    }

    public function setDryRun(bool $dryRun): SyncOptions
    {
        $this->dryRun = $dryRun;
        return $this;
    }

    public function isAll(): bool
    {
        return $this->all;
    }

    public function setAll(bool $all): SyncOptions
    {
        $this->all = $all;
        return $this;
    }

    public function isNoFiles(): bool
    {
        return $this->noFiles;
    }

    public function setNoFiles(bool $noFiles): SyncOptions
    {
        $this->noFiles = $noFiles;
        return $this;
    }

    public function isNoDatabases(): bool
    {
        return $this->noDatabases;
    }

    public function setNoDatabases(bool $noDatabases): SyncOptions
    {
        $this->noDatabases = $noDatabases;
        return $this;
    }
}
