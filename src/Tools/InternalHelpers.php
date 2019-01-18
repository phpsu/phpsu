<?php
declare(strict_types=1);

namespace PHPSu\Tools;

use PHPSu\Controller;
use PHPSu\Process\CommandExecutor;
use PHPSu\Process\Process;

final class InternalHelpers
{

    private const FALLBACK_VERSION = 'console';

    public function getCurrentPHPSUVersion(): string
    {
        if ($this->isVendorPackage()) {
            return $this->getPhpSuVersionFromVendor();
        }
        if (EnvironmentUtility::isGitInstalled()) {
            return trim($this->getPhpSuVersionFromGitCommand());
        }
        if ($this->isGitFolderAvailable()) {
            return $this->getPhpSuVersionFromGitFolder();
        }
        return self::FALLBACK_VERSION;
    }

    private function isVendorPackage(): bool
    {
        return false;
    }

    private function getPhpSuVersionFromVendor(): string
    {
        return '';
    }

    private function getPhpSuVersionFromGitFolder(): string
    {
        $tags = new \DirectoryIterator(Controller::PHPSU_ROOT_PATH . '.git/refs/tags/');
        $folderArray = [];
        foreach ($tags as $tagFile) {
            if ($tagFile->isDot() || $tagFile->isDir()) {
                continue;
            }
            $folderArray[$tagFile->getMTime()] = $tagFile->getFilename();
        }
        if (!empty($folderArray)) {
            ksort($folderArray);
            return array_shift($folderArray);
        }
        throw new \RuntimeException(
            'The git folder is available but does not seem to have any tags available - did you fetch all latest tags?'
        );
    }

    private function getPhpSuVersionFromGitCommand(): string
    {
        $executor = new CommandExecutor();
        $gitCommand = $executor->executeDirectly('git tag | tail -1');
        $response = $executor->getCommandReturnBuffer($gitCommand, false);
        if ($executor->getCommandReturnBuffer($gitCommand, true) === Process::ERR) {
            throw new \RuntimeException(sprintf(
                'The git command resulted in an error despite git being installed - did you set it up correctly? %s',
                $response
            ));
        }
        return $response;
    }

    private function isGitFolderAvailable(): bool
    {
        return file_exists(Controller::PHPSU_ROOT_PATH . '.git/');
    }
}
