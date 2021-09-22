<?php

declare(strict_types=1);

use Composer\InstalledVersions;
use PHPSu\Cli\MysqlCliCommand;
use PHPSu\Cli\InfoCliCommand;
use PHPSu\Cli\SshCliCommand;
use PHPSu\Cli\SyncCliCommand;
use PHPSu\Command\CommandGenerator;
use PHPSu\Config\ConfigurationLoader;
use PHPSu\Config\ConfigurationLoaderInterface;
use PHPSu\Config\GlobalConfig;
use PHPSu\Controller;
use PHPSu\ControllerInterface;
use PHPSu\Process\CommandExecutor;
use PHPSu\Tools\EnvironmentUtility;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Application;

use function DI\create;
use function DI\factory;
use function DI\env;
use function DI\get;

return [
    'config' => env('PHPSU_CONFIG_FILE', 'phpsu-config.php'),
    'commands' => [
        MysqlCliCommand::class,
        InfoCliCommand::class,
        SshCliCommand::class,
        SyncCliCommand::class,
    ],
    CommandGenerator::class => create(CommandGenerator::class)->constructor(get(GlobalConfig::class)),
    ControllerInterface::class => create(Controller::class)->constructor(
        get(CommandGenerator::class),
        get(CommandExecutor::class),
    ),
    EnvironmentUtility::class => create(EnvironmentUtility::class)->constructor(
        get(CommandGenerator::class),
        get(CommandExecutor::class),
    ),
    ConfigurationLoaderInterface::class => create(ConfigurationLoader::class)->constructor(get('config')),
    GlobalConfig::class => factory(static function (ContainerInterface $container): GlobalConfig {
        return $container->get(ConfigurationLoaderInterface::class)->getConfig();
    }),
    Application::class => factory(static function (ContainerInterface $container): Application {
        $application = new Application(
            'phpsu',
            InstalledVersions::getVersion('phpsu/phpsu') ?? 'development'
        );
        foreach ($container->get('commands') as $command) {
            $application->add($container->get($command));
        }
        return $application;
    }),
];
