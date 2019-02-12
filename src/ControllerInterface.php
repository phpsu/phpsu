<?php
declare(strict_types=1);

namespace PHPSu;

use PHPSu\Config\GlobalConfig;
use Symfony\Component\Console\Output\OutputInterface;

interface ControllerInterface
{
    public function ssh(OutputInterface $output, GlobalConfig $config, SshOptions $options): int;

    public function sync(OutputInterface $output, GlobalConfig $config, SyncOptions $options): void;
}
