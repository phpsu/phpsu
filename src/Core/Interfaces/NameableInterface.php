<?php
declare(strict_types=1);

namespace PHPSu\Core\Interfaces;

interface NameableInterface
{
    public function getName(): string;

    public function setName(string $name);
}
