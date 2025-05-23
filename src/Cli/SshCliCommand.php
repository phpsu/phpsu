<?php

declare(strict_types=1);

namespace PHPSu\Cli;

use Exception;
use PHPSu\Config\AppInstance;
use PHPSu\Helper\StringHelper;
use PHPSu\Options\SshOptions;
use PHPSu\ShellCommandBuilder\ShellBuilder;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

use function in_array;

/**
 * @internal
 */
final class SshCliCommand extends AbstractCliCommand
{
    /** @var null|string[] */
    private ?array $instances = null;

    protected function configure(): void
    {
        $this->setName('ssh')
            ->setDescription('create SSH Connection')
            ->setHelp('Connect to AppInstance via SSH.')
            ->addOption('dry-run', 'd', InputOption::VALUE_NONE, 'Only show commands that would be run.')
            ->addOption('from', 'f', InputOption::VALUE_OPTIONAL, 'The Source AppInstance.', 'local')
            ->addArgument('destination', InputArgument::REQUIRED, 'The Destination AppInstance.')
            ->addArgument('commands', InputArgument::IS_ARRAY | InputArgument::OPTIONAL, 'Run commands on remote ssh', []);
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        /** @var string $default */
        $default = $input->hasArgument('destination') ? $input->getArgument('destination') ?? '' : '';
        $input->setArgument(
            'destination',
            StringHelper::findStringInArray($default, $this->getAppInstancesWithHost()) ?: $default
        );
    }

    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        $default = $input->hasArgument('destination') ? $input->getArgument('destination') : '';
        if (!$this->getAppInstancesWithHost()) {
            throw new Exception('You need to define at least one AppInstance besides local');
        }

        if (!in_array($default, $this->getAppInstancesWithHost(), true)) {
            $question = new ChoiceQuestion('Please select one of the AppInstances', $this->getAppInstancesWithHost());
            $question->setErrorMessage('AppInstance %s not found in Config.');
            $questionHelper = $this->getHelper('question');
            assert($questionHelper instanceof QuestionHelper);
            $destination = $questionHelper->ask($input, $output, $question);
            assert(is_scalar($destination));
            $output->writeln('You selected: ' . $destination);
            $input->setArgument('destination', $destination);
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var string $destination */
        $destination = $input->getArgument('destination');
        /** @var string $currentHost */
        $currentHost = $input->getOption('from');
        /** @var array<string> $commandArray */
        $commandArray = $input->getArgument('commands');
        $builder = ShellBuilder::new();
        foreach ($commandArray as $command) {
            $builder->addSingle($command, true);
        }

        return $this->controller->ssh(
            $output,
            $this->configurationLoader->getConfig(),
            (new SshOptions($destination))
                ->setCurrentHost($currentHost)
                ->setCommand($builder)
                ->setDryRun((bool)$input->getOption('dry-run'))
        );
    }

    /**
     * @return string[]
     */
    private function getAppInstancesWithHost(): array
    {
        if ($this->instances === null) {
            $this->instances = $this->configurationLoader->getConfig()->getAppInstanceNames(static fn(AppInstance $instance): bool => $instance->getHost() !== '');
        }

        return $this->instances;
    }
}
