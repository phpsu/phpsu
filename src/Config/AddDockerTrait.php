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
    /** @var bool */
    protected $executeInDocker = false;
    /** @var bool */
    protected $isSudoEnabled = false;
    /** @var string */
    protected $container = '';

    /**
     * @param bool $enable
     * @return static
     */
    public function executeInDocker(bool $enable)
    {
        $this->executeInDocker = $enable;
        return $this;
    }

    public function isDockerEnabled(): bool
    {
        return $this->executeInDocker;
    }

    /**
     * @param string $containerName
     * @return $this
     */
    public function setContainer(string $containerName)
    {
        $this->container = $containerName;
        return $this;
    }

    public function getContainer(): string
    {
        return $this->container;
    }

    /**
     * @param bool $isSudoEnabled
     * @return static
     */
    public function enableSudoForDocker(bool $isSudoEnabled)
    {
        $this->isSudoEnabled = $isSudoEnabled;
        return $this;
    }

    public function useSudo(): bool
    {
        return $this->isSudoEnabled;
    }
}
