<?php
declare(strict_types=1);

namespace Run;

use Codeception\Lib\Console\Output;
use PHPSu\Alpha\AppInstance;
use PHPSu\Alpha\FileSystem;
use PHPSu\Alpha\GlobalConfig;
use PHPSu\Alpha\SshConnection;
use PHPSu\Beta\TheInterface;
use PHPSu\Chi\Runner;
use Symfony\Component\Console\Output\OutputInterface;

require_once 'vendor/autoload.php';

$config = new GlobalConfig();
$config->addFilesystem((new FileSystem())->setName('A')->setPath('A'));
$config->addSshConnection((new SshConnection())->setHost('hosta')->setUrl('ssh://user:user@localhost:2208')->setIdentityFile('docker/testCaseD/id_rsa'));
$config->addAppInstance((new AppInstance())->setName('production')->setHost('hosta')->setPath('~/'));
$config->addAppInstance((new AppInstance())->setName('local'));
//(new Runner())->run($config, 'production', 'local', '');
//(new Runner())->runCli($config, 'production', 'local', '');


$output = new Output(['verbosity' => OutputInterface::VERBOSITY_VERBOSE]);
$sectionTop = $output->section();
$sectionMiddle = $output->section();
$sectionMiddle->writeln(str_repeat('-', 20), OutputInterface::OUTPUT_RAW);
$sectionBottom = $output->section();
$commands = [
    'sleep_' => 'sleep 1',
    'sleep ' => 'sleep 2.5 && Dbwaid biwaj',
    'sleep-' => 'sleep 2.1',
    'sleep~' => 'sleep 3',
];
(new TheInterface())->execute($commands, $sectionTop, $sectionBottom);
//(new TheInterface())->execute($commands, $output, $output);
