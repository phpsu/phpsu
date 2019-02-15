<?php
declare(strict_types=1);

namespace PHPSu\Helper;

use PHPSu\Controller;
use PHPSu\Exceptions\CommandExecutionException;
use PHPSu\Exceptions\EnvironmentException;
use PHPSu\Process\CommandExecutor;
use PHPSu\Tools\EnvironmentUtility;

final class InternalHelper
{

    public function getCurrentPHPSUVersion(): string
    {
      return $this->getPhpSuVersionFromVendor() ?? $this->getPhpSuVersionFromGit() ?? 'development';
    }
    {
        if ($this->isVendorPackage()) {
            return $this->getPhpSuVersionFromVendor();
        }
        if ((new EnvironmentUtility)->isGitInstalled()) {
            return trim($this->getPhpSuVersionFromGitCommand());
        }
        if ($this->isGitFolderAvailable()) {
            return $this->getPhpSuVersionFromGitFolder();
        }
        @trigger_error('Could not detect phpsu-version properly - using development instead', E_USER_NOTICE);
        return 'development';
    }

    private function isVendorPackage(): bool
    {
        return \defined('PHPSU_VENDOR_INSTALLATION');
    }

    private function getPhpSuVersionFromVendor(): string
    {
        return (new EnvironmentUtility())->getInstalledPackageVersion('phpsu/phpsu');
    }

    private function getPhpSuVersionFromGitFolder(): string
    {
        $file = file_get_contents(Controller::PHPSU_ROOT_PATH . '/.git/HEAD');
        if ($file === false) {
            throw new EnvironmentException('The git folder is available but the HEAD file does not seem to be readable');
        }
        return str_replace('ref: refs/heads/', '', $file);
    }

    private function getPhpSuVersionFromGitCommand(): string
    {
        $executor = new CommandExecutor();
        $gitCommand = $executor->executeDirectly('git rev-parse --abbrev-ref HEAD');
        if (!empty($gitCommand[1]) || $gitCommand[2] !== 0) {
            throw new CommandExecutionException(sprintf('The git command resulted in an error despite git being installed - did you set it up correctly? %s', $gitCommand[1]));
        }
        return $gitCommand[0];
    }

    private function isGitFolderAvailable(): bool
    {
        return file_exists(Controller::PHPSU_ROOT_PATH . '/.git/');
    }
}
