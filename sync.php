#!/usr/bin/env php
<?php

declare(strict_types=1);

namespace Phpsu\Phpsu;

require_once __DIR__ . '/vendor/autoload.php';

use Kanti\Secrets\Secrets;
use NunoMaduro\Collision\Handler;
use NunoMaduro\Collision\Provider;
use NunoMaduro\Collision\Writer;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

$output = new ConsoleOutput(OutputInterface::VERBOSITY_DEBUG);

(new Provider(null, new Handler(new Writer(output: $output))))->register();

new SyncCommand(new Config());
