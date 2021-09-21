<?php

declare(strict_types=1);

namespace PHPSu\Helper;

use PHPSu\Controller;
use PHPSu\Exceptions\EnvironmentException;
use PHPSu\Tools\EnvironmentUtility;

use function strpos;

/**
 * @internal
 */
final class ApplicationHelper
{
    public function getCurrentPHPSUVersion(): string
    {
        $gitPath = Controller::PHPSU_ROOT_PATH . '/.git/';
        return $this->getPhpSuVersionFromGlobals() ?? $this->getPhpSuVersionFromVendor() ?? $this->getPhpSuVersionFromGitFolder($gitPath) ?? 'development';
    }

    private function getPhpSuVersionFromGlobals(): ?string
    {
        $pharVersion = '@phpsu_version@';
        return strpos($pharVersion, '@') === 0 ? null : $pharVersion;
    }

    private function getPhpSuVersionFromVendor(): ?string
    {
        return (new EnvironmentUtility())->getInstalledPackageVersion('phpsu/phpsu');
    }

    private function getPhpSuVersionFromGitFolder(string $gitPath): ?string
    {
        if (!file_exists($gitPath)) {
            return null;
        }
        $file = file_get_contents($gitPath . '/HEAD') ?: '';
        return trim(str_replace('ref: refs/heads/', '', $file));
    }
}
