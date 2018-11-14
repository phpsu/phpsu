<?php
declare(strict_types=1);

namespace PHPSu\Configuration\RawConfiguration;

use PHPSu\Configuration\RawConfiguration\RawFilesystemDto as Item;
use PHPSu\Core\AbstractBag;
use PHPSu\Core\AbstractNameableBag;

class RawFilesystemBag extends AbstractNameableBag
{
    public function __construct(Item ...$filesystems)
    {
        parent::__construct($filesystems, Item::class);
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
