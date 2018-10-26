<?php
declare(strict_types=1);

namespace PHPSu\Configuration\ProcessedConfiguration;

use PHPSu\Configuration\ProcessedConfiguration\ProcessedFilesystemDto as Item;
use PHPSu\Core\AbstractBag;

class ProcessedFilesystemBag extends AbstractBag
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
