<?php
declare(strict_types=1);

namespace PHPSu\Config\Writer;

use PHPSu\Config\GlobalConfig;

final class ConfigurationWriter
{
    public function write(GlobalConfig $globalConfig, \SplFileObject $file)
    {
        $phpFile = $this->getPhpFile($globalConfig);
        $file->ftruncate(0);
        $file->fwrite($phpFile);
    }

    private function getPhpFile(GlobalConfig $globalConfig): string
    {
        $php = '<?php' . PHP_EOL;
        $php .= 'declare(strict_types=1);' . PHP_EOL;
        $php .= '' . PHP_EOL;
        $php .= '$globalConfig = new ' . GlobalConfig::class . ';' . PHP_EOL;
        $php .= '' . PHP_EOL;
        if ($globalConfig->getDefaultSshConfig() !== (new GlobalConfig())->getDefaultSshConfig()) {
            $defaultSshConfigPhp = '$globalConfig->setDefaultSshConfig(' . $this->assocArray($globalConfig->getDefaultSshConfig());
            $php .= $defaultSshConfigPhp . ');' . PHP_EOL;
            $php .= '' . PHP_EOL;
        }
        if ($globalConfig->getFileSystems()) {
            $php .= $this->getFilesystemPart($globalConfig->getFileSystems(), '$globalConfig');
            $php .= '' . PHP_EOL;
        }
        if ($globalConfig->getDatabases()) {
            $php .= $this->getDatabasePart($globalConfig->getDatabases(), '$globalConfig');
            $php .= '' . PHP_EOL;
        }
        if ($globalConfig->getSshConnections()->getConnections()) {
            foreach ($globalConfig->getSshConnections()->getConnections() as $sshConnection) {
                $sshConnectionPhp = '$globalConfig->addSshConnection('
                    . var_export($sshConnection->getHost(), true) . ', '
                    . var_export((string)$sshConnection->getUrl(), true);
                if ($sshConnection->getOptions()) {
                    $sshConnectionPhp .= ', ' . $this->array($sshConnection->getOptions());
                }
                $sshConnectionPhp .= ')';
                if ($sshConnection->getFrom()) {
                    $sshConnectionPhp .= PHP_EOL . '    ->setFrom(' . $this->array($sshConnection->getFrom()) . ')';
                }
                $php .= $sshConnectionPhp . ';' . PHP_EOL;
            }
            $php .= '' . PHP_EOL;
        }
        if ($globalConfig->getAppInstances()) {
            foreach ($globalConfig->getAppInstances() as $appInstance) {
                $variableName = '$appInstance' . ucfirst($this->variableName($appInstance->getName()));
                $appInstancePhp = $variableName . ' = $globalConfig->addAppInstance('
                    . var_export($appInstance->getName(), true)
                    . ', ' . var_export($appInstance->getHost(), true)
                    . ', ' . var_export($appInstance->getPath(), true)
                    . ');' . PHP_EOL;
                $appInstancePhp .= $this->getFilesystemPart($appInstance->getFilesystems(), $variableName);
                $appInstancePhp .= $this->getDatabasePart($appInstance->getDatabases(), $variableName);
                $php .= $appInstancePhp;
            }
            $php .= '' . PHP_EOL;
        }
        $php .= 'return $globalConfig;';
        return $php;
    }

    private function array(array $array): string
    {
        $elements = implode(
            ', ',
            array_map(
                static function ($element) {
                    return var_export($element, true);
                },
                $array
            )
        );
        return '[' . $elements . ']';
    }

    private function assocArray(array $array): string
    {
        $resultingArray = [];
        foreach ($array as $key => $value) {
            $resultingArray[] = PHP_EOL . '  ' . var_export($key, true) . ' => ' . var_export($value, true);
        }
        $elements = implode(',', $resultingArray);
        if ($elements) {
            $elements .= PHP_EOL;
        }
        return '[' . $elements . ']';
    }

    private function variableName(string $name): string
    {
        return preg_replace('/[^a-zA-Z0-9]/', '_', $name);
    }

    /**
     * @param \PHPSu\Config\FileSystem[] $fileSystems
     * @param string $variableName
     * @return string
     */
    private function getFilesystemPart(array $fileSystems, string $variableName): string
    {
        $resultingString = '';
        foreach ($fileSystems as $fileSystem) {
            $fileSystemPhp = $variableName . '->addFilesystem('
                . var_export($fileSystem->getName(), true)
                . ', ' . var_export($fileSystem->getPath(), true)
                . ')';
            if ($fileSystem->getExcludes()) {
                $fileSystemPhp .= PHP_EOL . '    ->addExcludes(' . $this->array($fileSystem->getExcludes()) . ')';
            }
            $resultingString .= $fileSystemPhp . ';' . PHP_EOL;
        }
        return $resultingString;
    }

    /**
     * @param $databases
     * @param string $variableName
     * @return string
     */
    private function getDatabasePart($databases, string $variableName): string
    {
        $resultingString = '';
        foreach ($databases as $database) {
            $databasePhp = $variableName . '->addDatabase('
                . var_export($database->getName(), true)
                . ', ' . var_export($database->getUrl(), true)
                . ')';
            if ($database->getExcludes()) {
                $databasePhp .= PHP_EOL . '    ->addExcludes(' . $this->array($database->getExcludes()) . ')';
            }
            $resultingString .= $databasePhp . ';' . PHP_EOL;
        }
        return $resultingString;
    }
}
