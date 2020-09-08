<?php

declare(strict_types=1);

namespace PHPSu\Config;

interface DockerTraitSupportInterface extends ConfigElement
{
    public function isDockerEnabled(): bool;

    public function getContainer(): string;

    public function useSudo(): bool;
}
