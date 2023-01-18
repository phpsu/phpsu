<?php
declare(strict_types=1);

namespace PHPSu\Cli;

use Composer\Question\StrictConfirmationQuestion;
use PHPSu\Config\AppInstance;
use PHPSu\Config\FileSystem;
use PHPSu\Config\GlobalConfig;
use PHPSu\Config\SshConnection;
use PHPSu\Config\Writer\ConfigurationWriter;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;

class InitCliCommand extends AbstractCliCommand
{
    const DO_EXIT = 'exit';
    const DO_ADD_APP = 'add appInstance';
    const DO_ADD_FS = 'add fileSystem';
    const DO_WRITE = 'save';

    /** @var InputInterface */
    private $input;
    /** @var OutputInterface */
    private $output;
    /** @var QuestionHelper */
    private $helper;

    /**
     * @return void
     */
    public function configure()
    {
        $this->setName('init')
            ->setDescription('initialise your phpsu-config.php')
            ->setHelp('Wizard to create your phpsu-config.php')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'overwrite current phpsu-config.php if it is there');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;
        $this->helper = $this->getHelper('question');


        $globalConfig = $this->getGlobalConfig();
        if (!$globalConfig) {
            return;
        }

        $exit = false;
        do {
            $this->printCurrentConfig($globalConfig, $this->output);
            try {
                switch ($this->askWhatToDo()) {
                    case static::DO_ADD_APP:
                        $appInstance = $this->askAppInstance();
                        $globalConfig->addAppInstanceObject($appInstance);
                        break;
                    case static::DO_ADD_FS:
                        $fileSystem = $this->askFileSystem();
                        $globalConfig->addFileSystemObject($fileSystem);
                        break;
                    case static::DO_WRITE:
                        $configurationWriter = new ConfigurationWriter();
                        $configurationWriter->write($globalConfig, new \SplFileObject('phpsu-config.php', 'w'));
                        break;
                    case static::DO_EXIT:
                        $exit = true;
                        break;
                    default:
                        throw new \Exception('Error answer is not in list');
                        break;
                }
            } catch (StopAskingException $exception) {
            }
            $this->checkHostConnections($globalConfig);
        } while (!$exit);
    }

    private function askFileSystem()
    {
        $this->output->writeln('<comment>We will now Setup an Filesystem</comment>');
        $fileSystem = new FileSystem();
        $this->askQuestion(
            'how would you like to <info>name</info> it',
            'your filesystem name is not valid',
            static function ($result) use ($fileSystem) {
                $fileSystem->setName($result);
            }
        );
        $this->askQuestion(
            'in what relative <info>path</info> is the folder to sync',
            'your filesystem name is not valid',
            static function ($result) use ($fileSystem) {
                $fileSystem->setPath($result);
            }
        );
        do {
        } while ($this->askQuestion(
            'what should be <info>exclude</info>d',
            '',
            static function ($result) use ($fileSystem) {
                if ($result) {
                    $fileSystem->addExclude($result);
                }
            },
            true
        ));

        return $fileSystem;
    }

    private function askAppInstance(): AppInstance
    {
        $this->output->writeln('<comment>We will now Setup an AppInstance</comment>');
        $appInstance = new AppInstance();
        $this->askQuestion(
            'how would you like to <info>name</info> it',
            'your app name is not valid',
            static function ($result) use ($appInstance) {
                $appInstance->setName($result);
            }
        );
        $this->askQuestion(
            'on what <info>host</info> is your appInstance',
            'your host name is not valid',
            static function ($result) use ($appInstance) {
                $appInstance->setHost($result);
            }
        );
        $this->askQuestion(
            'in what absolute <info>path</info> is your appInstance on the host',
            'your path is not valid',
            static function ($result) use ($appInstance) {
                $appInstance->setPath($result);
            }
        );
        return $appInstance;
    }

    private function askSshConnection(string $host): SshConnection
    {
        $sshConnection = new SshConnection();
        $sshConnection->setHost($host);
        /* TODO:
         * setFrom()
         * setOptions()
         */
        $this->askQuestion(
            'how do we get to the host <info>' . $host . '</info>' . PHP_EOL
            . 'like: <info>ssh://user@hostOrIp:22</info>',
            'your ssh connection url is invalid',
            static function ($result) use ($sshConnection) {
                $sshConnection->setUrl($result);
            }
        );
        return $sshConnection;
    }

    private function checkHostConnections(GlobalConfig $globalConfig)
    {
        foreach ($globalConfig->getAppInstances() as $appInstance) {
            $host = $appInstance->getHost();
            if ($host === '') {
                continue;
            }
            try {
                $globalConfig->getSshConnections()->getPossibilities($host);
            } catch (\Exception $exception) {
                try {
                    $sshConnection = $this->askSshConnection($host);
                    $globalConfig->addSshConnectionObject($sshConnection);
                } catch (StopAskingException $exception) {
                    $this->checkHostConnections($globalConfig);
                }
            }
        }
    }

    private function askQuestion(string $question, string $error, callable $validationFunction, bool $allowEmpty = false): string
    {
        $questionObject = new Question($question . '<info>?</info>' . PHP_EOL . '> ', '');
        $count = 0;
        do {
            $result = $this->helper->ask($this->input, $this->output, $questionObject);
            if ($result === '') {
                if ($allowEmpty) {
                    return $result;
                }
                $count++;
                if ($count >= 3) {
                    throw new StopAskingException();
                }
            }
            try {
                $validationFunction($result);
            } catch (\Exception $exception) {
                $this->output->writeln('<error>' . $error . ': ' . $exception->getMessage() . '</error>');
                $result = '';
            }
        } while (!$result);
        return $result;
    }

    /**
     * @return \PHPSu\Config\GlobalConfig|null
     */
    private function getGlobalConfig()
    {
        try {
            $globalConfig = $this->configurationLoader->getConfig();
        } catch (\RuntimeException $exception) {
            $globalConfig = null;
        }
        if ($globalConfig) {
            if (!$this->getOption($this->input, 'force')) {
                $question = new StrictConfirmationQuestion('we found a config would you like to <info>overwrite</info> it? y/N' . PHP_EOL . '> ', false);
                if (!$this->helper->ask($this->input, $this->output, $question)) {
                    return null;
                }
            }
        }
        return $globalConfig ?? new GlobalConfig();
    }

    private function printCurrentConfig(GlobalConfig $globalConfig, OutputInterface $output)
    {
        foreach ($globalConfig->getFileSystems() as $fileSystem) {
            $output->writeln(
                sprintf(
                    '<info>FS</info>: name: <info>%s</info> path: <fg=cyan>%s</> excludes: <fg=cyan>%s</> ',
                    $fileSystem->getName(),
                    $fileSystem->getPath(),
                    implode(',', $fileSystem->getExcludes())
                )
            );
        }
        foreach ($globalConfig->getDatabases() as $database) {
            $output->writeln(
                sprintf(
                    '<info>DB</info>: name: <info>%s</info> excludes: <fg=cyan>%s</> ',
                    $database->getName(),
                    implode(',', $database->getExcludes())
                )
            );
        }
        foreach ($globalConfig->getAppInstances() as $appInstance) {
            $connectionPart = '';
            if ($appInstance->getHost() !== '') {
                $connectionPart = sprintf(
                    ' sshConnection: <fg=cyan>%s</>',
                    implode(
                        ',',
                        array_map(
                            static function (SshConnection $connection): string {
                                return (string)$connection->getUrl();
                            },
                            $globalConfig->getSshConnections()->getPossibilities($appInstance->getHost())
                        )
                    )
                );
            }
            $output->writeln(
                sprintf(
                    '<info>AppInstance</info>: name: <info>%s</info> host: <fg=cyan>%s</> path: <fg=cyan>%s</>%s',
                    $appInstance->getName(),
                    $appInstance->getHost() ?: 'local',
                    $appInstance->getPath(),
                    $connectionPart
                )
            );
        }
    }

    private function askWhatToDo(): string
    {
        $question = new ChoiceQuestion(
            'What should we do<info>?</info>',
            [
                static::DO_EXIT,
                static::DO_ADD_FS,
                static::DO_ADD_APP,
                static::DO_WRITE,
            ],
            static::DO_EXIT
        );
        return $this->helper->ask($this->input, $this->output, $question);
    }
}
