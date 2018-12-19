<?php
declare(strict_types=1);

namespace PHPSu\Beta;

final class Process extends \Symfony\Component\Process\Process
{
    private $name = '';

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): Process
    {
        $this->name = $name;
        return $this;
    }
}
