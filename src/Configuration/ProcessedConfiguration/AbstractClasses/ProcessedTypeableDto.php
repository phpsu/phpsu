<?php
declare(strict_types=1);

namespace PHPSu\Configuration\ProcessedConfiguration\AbstractClasses;

abstract class ProcessedTypeableDto extends ProcessedNameableDto
{
    /**
     * @var string
     */
    private $type;

    public function __construct(string $name = '', string $type = '')
    {
        parent::__construct($name);
        if (strlen($type) <= 0) {
            throw new \InvalidArgumentException(static::class . ' needs a type');
        }
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
