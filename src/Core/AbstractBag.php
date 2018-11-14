<?php
declare(strict_types=1);

namespace PHPSu\Core;

use PHPSu\Core\Interfaces\NameableInterface;

abstract class AbstractBag implements \Iterator, \ArrayAccess
{
    protected $bagContent = [];
    private $itemClass = '';

    public function __construct(array $array, string $itemClass)
    {
        $this->itemClass = $itemClass;
        foreach ($array as $item) {
            $this[] = $item;
        }
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
        $key = $this->key();
        if ($key === null) {
            return false;
        }
        return isset($this->bagContent[$key]);
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

    public function offsetSet($offset, $item)
    {
        $this->bagContent[$item->getName()] = $item;
    }

    public function offsetUnset($offset)
    {
        unset($this->bagContent[$offset]);
    }

    public static function __set_state(array $data)
    {
        return new static(
            ...array_values($data['bagContent'] ?? [])
        );
    }
}
