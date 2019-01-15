<?php
declare(strict_types=1);

namespace PHPSu\Command;

interface CommandInterface
{
    public function getName(): string;

    public function generate(): string;
}
