<?php

namespace PHPSu\Actions;

use PHPSu\Core\AbstractBag;
use PHPSu\Actions\ActionInterface as Item;

class ActionList extends AbstractBag
{
    public function __construct(Item ...$databases)
    {
        parent::__construct($databases, Item::class);
    }

    public function current(): Item
    {
        return current($this->bagContent);
    }

    public function offsetGet($offset): Item
    {
        return $this->bagContent[$offset];
    }
}
