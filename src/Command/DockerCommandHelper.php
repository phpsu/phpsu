<?php

declare(strict_types=1);

namespace PHPSu\Command;

use PHPSu\Config\ConfigElement;
use PHPSu\ShellCommandBuilder\Exception\ShellBuilderException;
use PHPSu\ShellCommandBuilder\ShellBuilder;
use PHPSu\ShellCommandBuilder\ShellCommand;
use PHPSu\ShellCommandBuilder\ShellInterface;

final class DockerCommandHelper
{
    /**
     * @param ConfigElement $configElement
     * @param ShellInterface $command
     * @param bool $enableInteractive
     * @param array $variables sets environment variables directly to the docker command
     * @return ShellInterface
     * @throws ShellBuilderException
     */
    public static function wrapCommand(ConfigElement $configElement, ShellInterface $command, bool $enableInteractive, array $variables = []): ShellInterface
    {
        // is docker support enabled in configuration, uses method_exists because interfaces on traits is not allowed
        if (method_exists($configElement, 'isDockerEnabled') && method_exists($configElement, 'getContainer')) {
            if (!$configElement->isDockerEnabled()) {
                return $command;
            }
            $builder = ShellBuilder::new()
                ->createCommand('docker')
                ->addArgument('exec')
                ->addShortOption($enableInteractive ? 'it' : 'i');
            foreach ($variables as $key => $variable) {
                $builder->addShortOption('e', sprintf('%s=%s', $key, escapeshellarg($variable)));
            }
            $builder
                ->addArgument($configElement->getContainer())
                ->addArgument($command, false)
                ->addToBuilder();
            if (method_exists($configElement, 'useSudo') && $configElement->useSudo()) {
                return ShellBuilder::command('sudo')->addArgument($builder, false)->addToBuilder();
            }
            return $builder;
        }
        return $command;
    }
}
