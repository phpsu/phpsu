<?php
declare(strict_types=1);

namespace PHPSu\Cli;

use PHPSu\Config\AppInstance;
use PHPSu\Config\ConfigurationLoaderInterface;
use PHPSu\ControllerInterface;
use PHPSu\Helper\StringHelper;
use PHPSu\SshOptions;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

final class SshCliCommand extends Command
{
    /** @var ConfigurationLoaderInterface */
    private $configurationLoader;
    /** @var ControllerInterface */
    private $controller;

    /** @var null|string[] */
    private $instances;


    public function __construct(ConfigurationLoaderInterface $configurationLoader, ControllerInterface $controller)
    {
        parent::__construct();
        $this->configurationLoader = $configurationLoader;
        $this->controller = $controller;
    }

    protected function configure(): void
    {
        $this->setName('ssh')
            ->setDescription('create SSH Connection')
            ->setHelp('Connect to AppInstance via SSH.')
            ->addOption('dry-run', 'd', InputOption::VALUE_NONE, 'Only show commands that would be run.')
            ->addOption('from', 'f', InputOption::VALUE_OPTIONAL, 'Only show commands that would be run.', 'local')
            ->addArgument('destination', InputArgument::REQUIRED, 'The Destination AppInstance.')
            ->addArgument('commands', InputArgument::IS_ARRAY | InputArgument::OPTIONAL, 'The Destination AppInstance.', []);
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $default = $input->hasArgument('destination') ? $input->getArgument('destination') : '';
        if ($default) {
            $input->setArgument(
                'destination',
                StringHelper::findStringInArray($default, $this->getAppInstancesWithHost()) ?: $default
            );
        }
    }

    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        $default = $input->hasArgument('destination') ? $input->getArgument('destination') : '';
        if (!\in_array($default, $this->getAppInstancesWithHost(), true)) {
            $question = new ChoiceQuestion('Please select one of the AppInstances', $this->getAppInstancesWithHost());
            $question->setErrorMessage('AppInstance %s not found in Config.');
            $destination = $this->getHelper('question')->ask($input, $output, $question);
            $output->writeln('You selected: ' . $destination);
            $input->setArgument('destination', $destination);
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        return $this->controller->ssh(
            $output,
            $this->configurationLoader->getConfig(),
            (new SshOptions($input->getArgument('destination')))
                ->setCurrentHost($input->getOption('from'))
                ->setCommand(implode(' ', $input->getArgument('commands')))
                ->setDryRun($input->getOption('dry-run'))
        );
    }

    /**
     * @return string[]
     */
    protected function getAppInstancesWithHost(): array
    {
        if ($this->instances === null) {
            $this->instances = $this->configurationLoader->getConfig()->getAppInstanceNames(function (AppInstance $instance) {
                return $instance->getHost() !== '';
            });
        }
        return $this->instances;
    }
}
