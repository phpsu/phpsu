<?php
declare(strict_types=1);

namespace PHPSu;

use PHPSu\Config\GlobalConfig;
use Symfony\Component\Console\Output\OutputInterface;

interface ControllerInterface
{
    public function ssh(OutputInterface $output, GlobalConfig $config, string $destination, string $currentHost, string $command, bool $dryRun): int;

    public function sync(OutputInterface $output, GlobalConfig $config, string $form, string $to, string $currentHost, bool $dryRun, bool $all, bool $noFiles, bool $noDatabases): void;
}
