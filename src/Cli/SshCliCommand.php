<?php
declare(strict_types=1);

namespace PHPSu\Cli;

use PHPSu\Config\AppInstance;
use PHPSu\Config\ConfigurationLoader;
use PHPSu\Controller;
use PHPSu\Helper\StringHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

final class SshCliCommand extends Command
{
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

    /**
     * Interacts with the user.
     *
     * This method is executed before the InputDefinition is validated.
     * This means that this is the only place where the command can
     * interactively ask for values of missing required arguments.
     *
     * @param InputInterface $input An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     */
    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        $configuration = (new ConfigurationLoader())->getConfig();
        $instances = $configuration->getAppInstanceNames();

        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');
        $default = $input->hasArgument('destination') ? $input->getArgument('destination') : '';
        $instanceName = StringHelper::findStringInArray($default, $instances);
        if ($instanceName) {
            $input->setArgument('destination', $instanceName);
            return;
        }
        $question = new ChoiceQuestion(
            'Please select one of the AppInstances',
            $instances,
            0
        );
        $question->setErrorMessage('AppInstance %s not found in Config.');
        $destination = $helper->ask($input, $output, $question);
        $output->writeln('You selected: ' . $destination);
        $input->setArgument('destination', $destination);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $controller = new Controller($output, (new ConfigurationLoader())->getConfig());
        return $controller->ssh(
            $input->getArgument('destination'),
            $input->getOption('from'),
            implode(' ', $input->getArgument('commands')),
            $input->getOption('dry-run')
        );
    }
}
