<?php
declare(strict_types=1);

namespace PHPSu\Tools;

use PHPSu\Process\CommandExecutor;
use PHPSu\Process\Process;

final class EnvironmentUtility
{
    public static function isRsyncInstalled(): bool
    {
        return self::isCommandInstalled('rsync');
    }

    public static function isMysqlDumpInstalled(): bool
    {
        return self::isCommandInstalled('mysqldump');
    }

    public static function isCommandInstalled(string $command): bool
    {
        $executor = new CommandExecutor();
        $output = $executor->executeDirectly($command);
        $result = $executor->getCommandReturnBuffer($output, false);
        return  $executor->getCommandReturnBuffer($output, true) === Process::OUT
            && stripos(trim($result), 'not found') === false;
    }

    public static function getRsyncVersion(): string
    {
        $executor = new CommandExecutor();
        $command = $executor->executeDirectly("rsync --version | sed -n '1s/^rsync *version \\([0-9.]*\\).*\$/\\1/p'");
        return trim($executor->getCommandReturnBuffer($command, false));
    }

    public static function getMysqlDumpVersion(): array
    {
        $executor = new CommandExecutor();
        $output = $executor->executeDirectly('mysqldump -V');
        preg_match_all(
            '/(.*Ver (?\'dump\'[\d.a-z]+).*)(.*Distrib (?\'mysql\'[\d.a-z]+).*)/m',
            trim($executor->getCommandReturnBuffer($output)),
            $matches,
            PREG_SET_ORDER,
            0
        );
        return [
            'mysqlVersion' => $matches[0]['mysql'],
            'dumpVersion' => $matches[0]['dump'],
        ];
    }

    public static function isGitInstalled(): bool
    {
        return self::isCommandInstalled('git');
    }

    public static function isWindows(): bool
    {
        return stripos(PHP_OS, 'WIN') === 0;
    }
}
