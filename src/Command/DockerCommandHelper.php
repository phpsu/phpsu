<?php

declare(strict_types=1);

namespace PHPSu\Command;

use PHPSu\Config\ConfigElement;
use PHPSu\Config\DockerTraitSupportInterface;
use PHPSu\ShellCommandBuilder\Exception\ShellBuilderException;
use PHPSu\ShellCommandBuilder\ShellBuilder;
use PHPSu\ShellCommandBuilder\ShellCommand;
use PHPSu\ShellCommandBuilder\ShellInterface;

final class DockerCommandHelper
{
    /**
     * @param array<string, string> $variables sets environment variables directly to the docker command
     * @throws ShellBuilderException
     */
    public static function wrapCommand(ConfigElement $configElement, ShellInterface $command, bool $enableInteractive, array $variables = []): ShellInterface
    {
        // interfaces on traits would make this much less configuration
        if ($configElement instanceof DockerTraitSupportInterface) {
            if (!$configElement->isDockerEnabled()) {
                return $command;
            }

            $builder = ShellBuilder::new()
                ->createCommand('docker')
                ->addArgument('exec')
                ->addShortOption($enableInteractive ? 'it' : 'i')
                ->if((bool)$variables, static function (ShellCommand $command) use ($variables): ShellCommand {
                    foreach ($variables as $key => $variable) {
                        $command->addShortOption('e', sprintf('%s=%s', $key, escapeshellarg($variable)));
                    }

                    return $command;
                })
                ->addArgument($configElement->getContainer())
                ->addArgument($command, false)
                ->addToBuilder();
            if ($configElement->useSudo()) {
                return ShellBuilder::command('sudo')->addArgument($builder, false)->addToBuilder();
            }

            return $builder;
        }

        return $command;
    }
}
