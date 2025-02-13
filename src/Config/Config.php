<?php

declare(strict_types=1);

namespace Phpsu\Phpsu\Config;

use InvalidArgumentException;

final readonly class Config
{
    private function __construct(
        public array $instances,
        public string $sshConfig = '~/.ssh/config',
        public ?string $sshKnownHostFile = null,
        public array $syncModes = [],
    ) {
    }

    public static function create(
        string $sshConfig = '~/.ssh/config',
        ?string $sshKnownHostFile = null,
        array $syncModes = [],
        array $instances = [],
    ): Config {
        if (count($instances) === 0) {
            throw new InvalidArgumentException('At least one instance is required');
        }

        (fn(Instance ...$instances): int => 0)(...$instances);
        (fn(SyncMode ...$syncMode): int => 0)(...$syncModes);
        return new Config(
            instances: $instances,
            sshConfig: $sshConfig,
            sshKnownHostFile: $sshKnownHostFile,
            syncModes: $syncModes,
        );
    }
}
