<?php
declare(strict_types=1);

namespace PHPSu\Delta;

use PHPSu\Alpha\AppInstance;
use PHPSu\Alpha\GlobalConfig;
use PHPSu\Console\AbstractCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

final class SshCommand extends AbstractCommand
{
    protected function configure(): void
    {
        $this->setName('ssh')
            ->setDescription('create SSH Connection')
            ->setHelp('Connect to AppInstance via SSH.')
            ->addOption('dry-run', 'd', InputOption::VALUE_NONE, 'Only show commands that would be run.')
            ->addOption('from', 'f', InputOption::VALUE_OPTIONAL, 'Only show commands that would be run.', 'local')
            ->addArgument('destination', InputArgument::REQUIRED, 'The Destination AppInstance.');
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
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $configuration = (new \PHPSu\Foxtrot\ConfigurationLoader())->getConfig();
        $appInstances = $configuration->getAppInstances()->getAll();
        $appInstances = array_filter($appInstances, function (AppInstance $instance) {
            return $instance->getHost() !== '';
        });
        $instances = array_keys($appInstances);

        $helper = $this->getHelper('question');
        $default = $input->hasArgument('destination') ? $input->getArgument('destination') : '';
        $defaultInt = array_search($default, $instances, true);
        if (!is_int($defaultInt)) {
            $question = new ChoiceQuestion(
                'Please select one of the AppInstances',
                $instances,
                0
            );
            $question->setErrorMessage('AppInstance %s not found in Config.');
            $destination = $helper->ask($input, $output, $question);
            $output->writeln('You have just selected: ' . $destination);
            $input->setArgument('destination', $destination);
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('TODO IMPLEMENT ' . json_encode($input->getArguments(), JSON_PRETTY_PRINT));
        return 0;
    }
}