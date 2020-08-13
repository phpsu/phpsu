<?php

declare(strict_types=1);

namespace PHPSu\Cli;

use Exception;
use PHPSu\Config\AppInstance;
use PHPSu\Helper\StringHelper;
use PHPSu\Options\MysqlOptions;
use PHPSu\Options\SshOptions;
use PHPSu\ShellCommandBuilder\ShellBuilder;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

use function in_array;

/**
 * @internal
 */
final class MysqlCliCommand extends AbstractCliCommand
{
    /** @var null|string[] */
    private $instances;


    protected function configure(): void
    {
        $this->setName('mysql')
            ->setDescription('connect to configured database')
            ->setHelp('Connect to Database of AppInstance via SSH.' . PHP_EOL . '(connects from the executing location)')
            ->addOption('dry-run', 'd', InputOption::VALUE_NONE, 'just display the commands.')
            ->addOption('database', 'b', InputArgument::OPTIONAL, 'Which Database to connect to')
            ->addArgument('instance', InputArgument::REQUIRED, 'Which AppInstance to connect to')
            ->addArgument('mysqlcommand', InputArgument::OPTIONAL, 'Execute a mysql command instead of connecting to it');
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        /** @var string $default */
        $default = $input->hasArgument('instance') ? $this->getArgument($input, 'instance') ?? '' : '';
        $input->setArgument(
            'instance',
            StringHelper::findStringInArray($default, $this->getAppInstancesWithHost()) ?: $default
        );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     * @throws Exception
     */
    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        $default = $input->hasArgument('instance') ? $this->getArgument($input, 'instance') : '';
        if (!in_array($default, $this->getAppInstancesWithHost(), true)) {
            $question = new ChoiceQuestion('Please select one of the AppInstances', $this->getAppInstancesWithHost());
            $question->setErrorMessage('AppInstance %s not found in Config.');
            $destination = $this->getHelper('question')->ask($input, $output, $question);
            $output->writeln('You selected: ' . $destination);
            $input->setArgument('instance', $destination);
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $instance = $this->getArgument($input, 'instance');
        $mysqlCommand = $this->getArgument($input, 'mysqlcommand') ?: '';
        /** @var string|null $database */
        $database = $this->getOption($input, 'database') ?: null;
        assert(is_string($instance) && is_string($mysqlCommand));
        return $this->controller->mysql(
            $output,
            $this->configurationLoader->getConfig(),
            (new MysqlOptions())
                ->setAppInstance($instance)
                ->setCommand($mysqlCommand)
                ->setDatabase($database)
                ->setDryRun((bool)$input->getOption('dry-run'))
        );
    }

    /**
     * todo: extract to utility
     *
     * @return string[]
     */
    private function getAppInstancesWithHost(): array
    {
        if ($this->instances === null) {
            $this->instances = $this->configurationLoader->getConfig()->getAppInstanceNames();
        }
        return $this->instances;
    }
}
