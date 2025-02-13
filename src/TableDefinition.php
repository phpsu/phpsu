<?php

declare(strict_types=1);

namespace Phpsu\Phpsu;

final readonly class TableDefinition
{
    public function __construct(
        public string $name,
        public ?string $primaryKey = 'uid',
        public ?string $timestampColumn = 'tstamp',
        public bool $primaryKeyAutoIncrement = true,
        public bool $isVolatile = false,
    ) {
    }
}
