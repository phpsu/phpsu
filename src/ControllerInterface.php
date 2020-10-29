<?php

declare(strict_types=1);

namespace PHPSu;

use PHPSu\Config\GlobalConfig;
use PHPSu\Options\MysqlOptions;
use PHPSu\Options\SshOptions;
use PHPSu\Options\SyncOptions;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Interface ControllerInterface
 * @package PHPSu
 * @internal
 */
interface ControllerInterface
{
    public function ssh(OutputInterface $output, GlobalConfig $config, SshOptions $options): int;

    public function mysql(OutputInterface $output, GlobalConfig $config, MysqlOptions $options): int;

    public function sync(OutputInterface $output, GlobalConfig $config, SyncOptions $options): void;

    public function checkSshConnection(OutputInterface $output, GlobalConfig $config, SyncOptions $options): void;
}
