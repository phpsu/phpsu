<?php
declare(strict_types=1);

namespace PHPSu\Configuration\Loader;

use PHPSu\Configuration\RawConfiguration\RawConfigurationDto;

abstract class AbstractConfigurationLoader
{
    /**
     * @var array
     */
    protected $options;

    public function __construct(array $options = [], array $defaultOptions = [])
    {
        $this->options = array_merge($defaultOptions, $options);
    }

    abstract public function getRawConfiguration(): RawConfigurationDto;
}
