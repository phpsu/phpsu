<?php
declare(strict_types=1);

namespace PHPSu\Alpha;

final class RsyncConfig
{
    public static function fromGlobal(\stdClass $global, string $fromInstanceName, string $toInstanceName)
    {
        $fromInstance = $global->appInstances->{$fromInstanceName};
        $toInstance = $global->appInstances->{$toInstanceName};
        $result = [];
        foreach ($global->fileSystems as $fileSystemName => $fileSystem) {
            $result[] = RsyncCommand::fromAppInstances($fromInstance, $toInstance, $fileSystem);
        }
        return $result;
    }
}
