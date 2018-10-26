<?php
declare(strict_types=1);

namespace PHPSu\Configuration\ProcessedConfiguration;

use PHPSu\Configuration\ProcessedConfiguration\ProcessedHostDto as Item;
use PHPSu\Core\AbstractBag;

class ProcessedHostBag extends AbstractBag
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
