<?php
declare(strict_types=1);

namespace PHPSu\Config;

final class ConfigurationLoader
{
    public function getConfig(): GlobalConfig
    {
        return require getcwd() . '/phpsu-config.php';
    }
}
