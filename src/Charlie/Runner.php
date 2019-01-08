<?php
declare(strict_types=1);

namespace PHPSu\Charlie;

use Codeception\Lib\Console\Output;
use PHPSu\Alpha\GlobalConfig;
use PHPSu\Alpha\TheInterface as TheAlphaInterface;
use PHPSu\Beta\TheInterface as TheBetaInterface;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;

final class Runner
{
    public function run(GlobalConfig $config, string $form, string $to, string $currentHost): void
    {
        $alpha = new TheAlphaInterface();
        $commands = $alpha->getCommands($config, $form, $to, $currentHost);
        $beta = new TheBetaInterface();
        $beta->execute($commands, new NullOutput(), new NullOutput());
    }

    public function runCli(GlobalConfig $config, string $form, string $to, string $currentHost): void
    {
        $alpha = new TheAlphaInterface();
        $commands = $alpha->getCommands($config, $form, $to, $currentHost);
        $beta = new TheBetaInterface();
        $output = new Output(['verbosity' => OutputInterface::VERBOSITY_VERBOSE]);
        $sectionTop = $output->section();
        $sectionMiddle = $output->section();
        $sectionMiddle->writeln(str_repeat('-', 20), OutputInterface::OUTPUT_RAW);
        $sectionBottom = $output->section();
        $beta->execute($commands, $sectionTop, $sectionBottom);
    }
}
