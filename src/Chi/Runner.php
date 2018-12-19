<?php
declare(strict_types=1);

namespace PHPSu\Chi;

use PHPSu\Alpha\GlobalConfig;
use PHPSu\Alpha\TheInterface as TheAlphaInterface;
use PHPSu\Beta\TheInterface as TheBetaInterface;

final class Runner
{
    public function run(GlobalConfig $config, string $form, string $to, string $currentHost): void
    {
        $alpha = new TheAlphaInterface();
        $commands = $alpha->getCommands($config, $form, $to, $currentHost);
        $beta = new TheBetaInterface();
        $beta->execute($commands);
    }
}
