<?php
declare(strict_types=1);

namespace PHPSu\Core;

use PHPSu\Core\Interfaces\NameableInterface;

abstract class AbstractNameableBag extends AbstractBag
{
    protected $bagContent = [];
    private $itemClass = '';

    public function __construct(array $array, string $itemClass)
    {
        if (!in_array(NameableInterface::class, class_implements($itemClass))) {
            throw new \InvalidArgumentException('class ' . $itemClass . ' is not compatible with ' . NameableInterface::class);
        }
        parent::__construct($array, $itemClass);
    }

    public function offsetSet($offset, $item)
    {
        if (!($item instanceof NameableInterface) || !($item instanceof $this->itemClass) || isset($result[$item->getName()])) {
            throw new \InvalidArgumentException('one ' . static::class . ' can only hold ' . $this->itemClass . ' with unique names');
        }
        parent::offsetSet($offset, $item);
    }
}
