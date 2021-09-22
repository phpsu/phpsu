<?php

declare(strict_types=1);

namespace PHPSu\Cli;

use PHPSu\Helper\StringHelper;
use PHPSu\Options\MysqlOptions;
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
    private ?array $instances = null;

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
        $default = $input->hasArgument('instance') ? $input->getArgument('instance') ?? '' : '';
        $input->setArgument(
            'instance',
            StringHelper::findStringInArray($default, $this->getAppInstancesWithHost()) ?: $default
        );
    }

    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        $default = $input->hasArgument('instance') ? $input->getArgument('instance') : '';
        $appInstancesWithHost = $this->getAppInstancesWithHost();
        if (!in_array($default, $appInstancesWithHost, true)) {
            $question = new ChoiceQuestion('Please select one of the AppInstances', $appInstancesWithHost);
            $question->setErrorMessage('AppInstance %s not found in Config.');
            $destination = $this->getHelper('question')->ask($input, $output, $question);
            $output->writeln('You selected: ' . $destination);
            $input->setArgument('instance', $destination);
        }
        $default = $input->hasOption('database') ? $input->getOption('database') : '';
        $instance = $input->getArgument('instance');
        assert(is_string($instance));
        $databases = $this->getDatabasesForAppInstance($instance);
        if (count($databases) > 1 && !in_array($default, $databases, true)) {
            $question = new ChoiceQuestion('Please select one of the Databases', $databases);
            $question->setErrorMessage('Database %s not found in Config.');
            $database = $this->getHelper('question')->ask($input, $output, $question);
            $output->writeln('You selected: ' . $database);
            $input->setOption('database', $database);
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $instance = $input->getArgument('instance');
        $mysqlCommand = $input->getArgument('mysqlcommand') ?: '';
        /** @var string|null $database */
        $database = $input->getOption('database') ?: null;
        assert(is_string($instance) && is_string($mysqlCommand));
        return $this->controller->mysql(
            $output,
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
            $this->instances = $this->config->getAppInstanceNames();
        }
        return $this->instances;
    }

    /**
     * @param string $appInstance
     * @return string[]
     */
    private function getDatabasesForAppInstance(string $appInstance): array
    {
        return $this->config->getAppInstance($appInstance)->getDatabaseNames();
    }
}
