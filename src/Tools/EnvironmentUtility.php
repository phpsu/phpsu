<?php

declare(strict_types=1);

namespace PHPSu\Tools;

use PHPSu\Controller;
use PHPSu\Exceptions\CommandExecutionException;
use PHPSu\Process\CommandExecutor;
use PHPSu\ShellCommandBuilder\ShellBuilder;
use stdClass;
use Symfony\Component\Process\Exception\ProcessStartFailedException;

use function assert;
use function is_array;
use function is_object;
use function is_string;

/**
 * @internal
 */
final class EnvironmentUtility
{
    private string $phpsuRootPath = Controller::PHPSU_ROOT_PATH;


    public function getInstalledPackageVersion(string $packageName): ?string
    {
        $contents = file_get_contents($this->spotVendorPath() . '/composer/installed.json') ?: '';
        $activeInstallations = json_decode($contents, false);
        if (!($activeInstallations instanceof stdClass) || json_last_error() !== JSON_ERROR_NONE) {
            return null;
        }

        assert(is_array($activeInstallations->packages));

        foreach ($activeInstallations->packages as $installed) {
            assert($installed instanceof stdClass);
            assert(is_string($installed->name));
            if ($installed->name === $packageName) {
                assert(is_string($installed->version));
                return $installed->version;
            }
        }

        return null;
    }

    private function spotVendorPath(): string
    {
        if (file_exists($this->phpsuRootPath . '/../../autoload.php')) {
            // installed via composer require
            return $this->phpsuRootPath . '/../../';
        }

        // in dev installation
        return $this->phpsuRootPath . '/vendor/';
    }
}
