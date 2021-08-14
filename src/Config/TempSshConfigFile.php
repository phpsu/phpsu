<?php

declare(strict_types=1);

namespace PHPSu\Config;

use Exception;
use SplFileObject;

/**
 * @internal
 */
final class TempSshConfigFile extends SplFileObject
{
    private static string $fileName = '.phpsu/config/ssh_config';

    public function __construct()
    {
        $directory = dirname(self::$fileName);
        if (!file_exists($directory) && (!@mkdir($directory, 0777, true) && !is_dir($directory))) {
            throw new Exception(sprintf('Directory "%s" was not created', $directory));
        }
        if (!file_exists(self::$fileName)) {
            touch(self::$fileName);
        }
        parent::__construct(self::$fileName, 'w+');
    }
}
