<?php

declare(strict_types=1);

namespace Phpsu\Phpsu\Config;

final readonly class MySql
{
    private function __construct(
        public string $database,
        public string $user,
        public string $password,
        public ?string $host = '127.0.0.1',
        public ?int $port = 3306,
        public ?string $socket = '/var/run/mysqld/mysqld.sock',
        public ?string $sshHost = null,
    ) {
    }

    public static function tcp(
        string $database,
        string $user,
        string $password,
        string $host = '127.0.0.1',
        int $port = 3306,
        ?string $sshHost = null,
    ): self {
        return new self(
            database: $database,
            user: $user,
            password: $password,
            host: $host,
            port: $port,
            socket: null,
            sshHost: $sshHost,
        );
    }

    public static function socket(
        string $database,
        string $user,
        string $password,
        string $socket = '/var/run/mysqld/mysqld.sock',
        ?string $sshHost = null,
    ): self {
        return new self(
            database: $database,
            user: $user,
            password: $password,
            host: null,
            port: null,
            socket: $socket,
            sshHost: $sshHost,
        );
    }
}
