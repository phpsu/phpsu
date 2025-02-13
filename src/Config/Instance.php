<?php

declare(strict_types=1);

namespace Phpsu\Phpsu\Config;

use Filesystem;
use MySql;

final readonly class Instance
{
    /**
     * @param array<string, \Phpsu\Phpsu\Config\Filesystem> $filesystem
     * @param array<string, \Phpsu\Phpsu\Config\MySql> $database
     */
    private function __construct(
        public array $filesystem,
        public array $database,
        public ?string $sshHost = null,
    ) {
    }

    /**
     * @param \Phpsu\Phpsu\Config\Filesystem|array<string, \Phpsu\Phpsu\Config\Filesystem> $filesystem
     * @param \Phpsu\Phpsu\Config\MySql|array<string, \Phpsu\Phpsu\Config\MySql> $database
     */
    public static function create(
        \Phpsu\Phpsu\Config\Filesystem|array $filesystem,
        \Phpsu\Phpsu\Config\MySql|array $database,
        ?string $sshHost = null,
    ): self {
        if ($filesystem instanceof \Phpsu\Phpsu\Config\Filesystem) {
            $filesystem = ['default' => $filesystem];
        }

        if ($database instanceof \Phpsu\Phpsu\Config\MySql) {
            $database = ['default' => $database];
        }

        return new self(
            filesystem: $filesystem,
            database: $database,
            sshHost: $sshHost,
        );
    }
}
