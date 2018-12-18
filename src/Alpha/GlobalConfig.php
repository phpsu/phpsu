<?php
declare(strict_types=1);

namespace PHPSu\Alpha;

final class GlobalConfig
{
    /** @var \stdClass */
    public $fileSystems;
    /** @var SshConnections */
    public $sshConnections;
    /** @var \stdClass */
    public $appInstances;
    /** @var \stdClass */
    public $databases;
}
