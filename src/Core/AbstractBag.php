<?php
declare(strict_types=1);

namespace PHPSu\Core;

abstract class AbstractBag implements \Iterator, \ArrayAccess
{
    protected $bagContent = [];

    public function __construct(array $array, string $itemClass)
    {
        $result = [];
        foreach ($array as $item) {
            if (!($item instanceof $itemClass) && isset($result[$item->getName()])) {
                throw new \InvalidArgumentException('one ' . static::class . ' can only hold ' . $itemClass . ' with unique names');
            }
            $result[$item->getName()] = $item;
        }
        $this->bagContent = $array;
    }

    abstract public function current();

    public function next()
    {
        next($this->bagContent);
    }

    public function key()
    {
        return key($this->bagContent);
    }

    public function valid()
    {
        return isset($this->bagContent[$this->key()]);
    }

    public function rewind()
    {
        reset($this->bagContent);
    }

    public function offsetExists($offset)
    {
        return isset($this->bagContent[$offset]);
    }

    abstract public function offsetGet($offset);

//    abstract public function offsetSet($offset, $value);

    public function offsetUnset($offset)
    {
        unset($this->bagContent[$offset]);
    }
}
