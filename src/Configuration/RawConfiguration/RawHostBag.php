<?php
declare(strict_types=1);

namespace PHPSu\Configuration\RawConfiguration;

use PHPSu\Configuration\RawConfiguration\RawHostDto as Item;
use PHPSu\Core\AbstractBag;

class RawHostBag extends AbstractBag
{
    public function __construct(Item ...$hosts)
    {
        parent::__construct($hosts, Item::class);
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