<?php

declare(strict_types=1);

namespace PHPSu\Command;

use PHPSu\ShellCommandBuilder\ShellBuilder;

/**
 * Interface CommandInterface
 * @package PHPSu\Command
 */
interface CommandInterface
{
    public function getName(): string;

    public function generate(ShellBuilder $shellBuilder): ShellBuilder;
}
