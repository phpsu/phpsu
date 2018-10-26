<?php
declare(strict_types=1);

namespace PHPSu\Configuration\RawConfiguration\AbstractClasses;

abstract class RawTypeableDto extends RawNameableDto
{
    /**
     * @var string
     */
    private $type;

    public function __construct(string $name = '', string $type = '')
    {
        parent::__construct($name);
        $this->type = $type;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type)
    {
        $this->type = $type;
    }
}
