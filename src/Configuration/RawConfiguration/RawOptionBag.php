<?php
declare(strict_types=1);

namespace PHPSu\Configuration\RawConfiguration;

class RawOptionBag implements \Iterator, \ArrayAccess
{
    protected $optionValues = [];

    public function current(): string
    {
        return current($this->optionValues);
    }

    public function next()
    {
        next($this->optionValues);
    }

    public function key()
    {
        return key($this->optionValues);
    }

    public function valid()
    {
        return $this->current() !== false;
    }

    public function rewind()
    {
        reset($this->optionValues);
    }

    public function offsetExists($offset)
    {
        return isset($this->optionValues[$offset]);
    }

    public function offsetGet($offset): string
    {
        return $this->optionValues[$offset];
    }

    public function offsetSet($offset, $value)
    {
        if (!is_string($value)) {
            throw new \InvalidArgumentException('a ' . static::class . ' can only hold strings');
        }
        return isset($this->optionValues[$offset]);
    }

    public function offsetUnset($offset)
    {
        unset($this->optionValues[$offset]);
    }
}
