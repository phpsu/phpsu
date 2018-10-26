<?php
declare(strict_types=1);

namespace PHPSu\Configuration\RawConfiguration\AbstractClasses;

abstract class RawNameableDto
{
    /**
     * @var string
     */
    protected $name;

    public function __construct(string $name = '')
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name)
    {
        $this->name = $name;
    }
}
