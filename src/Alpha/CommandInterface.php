<?php
declare(strict_types=1);

namespace PHPSu\Alpha;

interface CommandInterface
{
    public function generate(): string;
}
