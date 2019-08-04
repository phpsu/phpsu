<?php

namespace PHPSu\Config;

interface ConnectionInterface
{
    /**
     * @return string[]
     */
    public function getOptions(): array;

    /**
     * @param string[] $options
     * @return ConnectionInterface
     */
    public function setOptions(array $options);
}
