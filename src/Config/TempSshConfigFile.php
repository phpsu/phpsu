<?php

declare(strict_types=1);

namespace PHPSu\Config;

use Exception;
use SplFileObject;

final class TempSshConfigFile extends SplFileObject
{
    public function __construct()
    {
        $fileName = '.phpsu/config/ssh_config';
        $directory = dirname($fileName);
        if (!file_exists($directory) && (!mkdir($directory, 0777, true) && !is_dir($directory))) {
            throw new Exception(sprintf('Directory "%s" was not created', $directory));
        }
        if (!file_exists($fileName)) {
            touch($fileName);
        }
        parent::__construct($fileName, 'w+');
    }
}
