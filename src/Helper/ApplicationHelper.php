<?php
declare(strict_types=1);

namespace PHPSu\Helper;

use PHPSu\Controller;
use PHPSu\Exceptions\EnvironmentException;
use PHPSu\Tools\EnvironmentUtility;

final class ApplicationHelper
{

    public function getCurrentPHPSUVersion(): string
    {
        return $this->getPhpSuVersionFromVendor() ?? $this->getPhpSuVersionFromGitFolder() ?? 'development';
    }

    private function getPhpSuVersionFromVendor(): ?string
    {
        return (new EnvironmentUtility())->getInstalledPackageVersion('phpsu/phpsu');
    }

    private function getPhpSuVersionFromGitFolder(): ?string
    {
        if (!$this->isGitFolderAvailable()) {
            return null;
        }
        $file = file_get_contents(Controller::PHPSU_ROOT_PATH . '/.git/HEAD');
        if ($file === false) {
            throw new EnvironmentException('The git folder is available but the HEAD file does not seem to be readable');
        }
        return str_replace('ref: refs/heads/', '', $file);
    }

    private function isGitFolderAvailable(): bool
    {
        return file_exists(Controller::PHPSU_ROOT_PATH . '/.git/');
    }
}
