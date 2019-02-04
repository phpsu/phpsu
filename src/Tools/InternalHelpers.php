<?php
declare(strict_types=1);

namespace PHPSu\Tools;

use PHPSu\Exceptions\EnvironmentException;
use PHPSu\Process\CommandExecutor;
use PHPSu\Process\Process;

final class InternalHelpers
{

    public function getCurrentPHPSUVersion(): string
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
        $file = file_get_contents(PHPSU_ROOT_PATH . '/.git/HEAD');
        if ($file === false) {
            throw new EnvironmentException('The git folder is available but the HEAD file does not seem to be readable');
        }
        return str_replace('ref: refs/heads/', '', $file);
    }

    private function getPhpSuVersionFromGitCommand(): string
    {
        $executor = new CommandExecutor();
        $gitCommand = $executor->executeDirectly('git symbolic-ref --short HEAD');
        $response = $executor->getCommandReturnBuffer($gitCommand, false);
        if ($executor->getCommandReturnBuffer($gitCommand, true) === Process::ERR) {
            throw new EnvironmentException(sprintf('The git command resulted in an error despite git being installed - did you set it up correctly? %s', $response));
        }
        return $response;
    }

    private function isGitFolderAvailable(): bool
    {
        return file_exists(PHPSU_ROOT_PATH . '/.git/');
    }
}
