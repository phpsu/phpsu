<?php

declare(strict_types=1);

namespace PHPSu\Config;

/**
 * Trait AddDockerTrait
 * @package PHPSu\Config
 * @internal
 */
trait AddDockerTrait
{
    protected bool $executeInDocker = false;

    protected bool $isSudoEnabled = false;

    protected string $container = '';

    public function executeInDocker(bool $enable): self
    {
        $this->executeInDocker = $enable;
        return $this;
    }

    public function isDockerEnabled(): bool
    {
        return $this->executeInDocker;
    }

    public function setContainer(string $containerName): self
    {
        $this->container = $containerName;
        return $this;
    }

    public function getContainer(): string
    {
        return $this->container;
    }

    public function enableSudoForDocker(bool $isSudoEnabled): self
    {
        $this->isSudoEnabled = $isSudoEnabled;
        return $this;
    }

    public function useSudo(): bool
    {
        return $this->isSudoEnabled;
    }
}
