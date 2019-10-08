<?php

declare(strict_types=1);

namespace PHPSu\Polyfills;

use PHPSu\Tools\EnvironmentUtility;
use Symfony\Component\Console\Output\ConsoleSectionOutput;

// @codeCoverageIgnoreStart
if (!class_exists(ConsoleSectionOutput::class) && version_compare((new EnvironmentUtility())->getSymfonyConsoleVersion(), '4.0.0', '<')) {
    require_once __DIR__ . '/ConsoleSectionOutput.php';
}
// @codeCoverageIgnoreEnd
