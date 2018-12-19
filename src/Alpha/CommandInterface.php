<?php
declare(strict_types=1);

namespace PHPSu\Alpha;

interface CommandInterface
{
    public function getName(): string;

    public function generate(): string;
}
