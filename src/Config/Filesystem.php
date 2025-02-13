<?php

declare(strict_types=1);

namespace Phpsu\Phpsu\Config;

final readonly class Filesystem
{
    private function __construct(
        public string $path,
        public ?string $sshHost = null,
    ) {
    }

    public static function create(
        string $path,
        ?string $sshHost = null,
    ): self {
        return new self(
            path: $path,
            sshHost: $sshHost,
        );
    }
}
