<?php
declare(strict_types=1);

namespace PHPSu\Cli;

use PHPSu\Helper\ApplicationHelper;
use PHPSu\Config\ConfigurationLoader;
use PHPSu\Controller;
use Symfony\Component\Console\Application;

final class PhpsuApplication
{
    public static function createApplication(): Application
    {
        $application = new Application('phpsu', (new ApplicationHelper())->getCurrentPHPSUVersion());
        $configurationLoader = new ConfigurationLoader();
        foreach (self::getAllCommandsInDirectory(__DIR__) as $class) {
            $fqClass = __NAMESPACE__ . '\\' . $class;
            $application->add(new $fqClass($configurationLoader, new Controller()));
        }
        self::getAllCommandsInDirectory(__DIR__);
        return $application;
    }

    private static function getAllCommandsInDirectory(string $directory): array
    {
        $result = array_filter(scandir($directory, SCANDIR_SORT_NONE), function ($name) {
            if (\in_array($name, ['.', '..'], true) || stripos($name, 'abstract') !== false) {
                return false;
            }
            return stripos($name, 'CliCommand') !== false;
        });
        return str_replace('.php', '', $result);
    }
}
