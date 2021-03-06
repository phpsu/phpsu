<?php

declare(strict_types=1);

namespace PHPSu\Config;

interface ConfigurationLoaderInterface
{
    public function getConfig(): GlobalConfig;
}
