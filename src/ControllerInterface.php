<?php

declare(strict_types=1);

namespace PHPSu;

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
    public function ssh(OutputInterface $output, SshOptions $options): int;

    public function mysql(OutputInterface $output, MysqlOptions $options): int;

    public function sync(OutputInterface $output, SyncOptions $options): void;

    public function checkSshConnection(OutputInterface $output, SyncOptions $options): void;
}
