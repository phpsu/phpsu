<?php

declare(strict_types=1);

namespace PHPSu\Tests\Command;

use PHPSu\Command\DatabaseCommand;
use PHPSu\Config\Compression\Bzip2Compression;
use PHPSu\Config\Compression\GzipCompression;
use PHPSu\Config\Database;
use PHPSu\Config\DatabaseConnectionDetails;
use PHPSu\Config\SshConfig;
use PHPSu\ShellCommandBuilder\ShellBuilder;
use PHPSu\ShellCommandBuilder\ShellCommand;
use PHPSu\ShellCommandBuilder\ShellInterface;
use PHPSu\Tests\ControllerTest;
use PHPUnit\Framework\TestCase;
use SplTempFileObject;
use Symfony\Component\Console\Output\OutputInterface;

final class DatabaseCommandTest extends TestCase
{
    public function testDatabaseCommandGenerate(): void
    {
        $sshConfig = new SshConfig();
        $sshConfig->setFile(new SplTempFileObject());
        $database = new DatabaseCommand();
        $fromConnection = (new Database())->setConnectionDetails(DatabaseConnectionDetails::fromUrlString('mysql://root:root@database/sequelmovie'));
        $toConnection = (new Database())->setConnectionDetails(DatabaseConnectionDetails::fromUrlString('mysql://root:root@127.0.0.1:2206/sequelmovie2'));
        $database->setSshConfig($sshConfig)
            ->setFromDatabase($fromConnection)
            ->setFromHost('hostc')
            ->setToDatabase($toConnection)
            ->setToHost('');
        $this->assertSame(
            "ssh -F 'php://temp' 'hostc' 'mysqldump " . ControllerTest::MYSQLDUMP_OPTIONS . " --host='\''database'\'' --user='\''root'\'' --password='\''root'\'' '\''sequelmovie'\'' | (echo '\''CREATE DATABASE IF NOT EXISTS `sequelmovie2`;USE `sequelmovie2`;'\'' && cat)" . ControllerTest::REMOVE_DEFINER_PART . "' | mysql --host='127.0.0.1' --port=2206 --user='root' --password='root'",
            (string)$database->generate(ShellBuilder::new())
        );
    }

    public function testDatabaseCommandWithoutDefinerInstructionsGenerate(): void
    {
        $sshConfig = new SshConfig();
        $sshConfig->setFile(new SplTempFileObject());
        $database = new DatabaseCommand();
        $fromConnection = (new Database())->setConnectionDetails(DatabaseConnectionDetails::fromUrlString('mysql://root:root@database/sequelmovie'));
        $fromConnection->setRemoveDefinerFromDump(true);
        $toConnection = (new Database())->setConnectionDetails(DatabaseConnectionDetails::fromUrlString('mysql://root:root@127.0.0.1:2206/sequelmovie2'));
        $database->setSshConfig($sshConfig)
            ->setFromDatabase($fromConnection)
            ->setFromHost('hostc')
            ->setToDatabase($toConnection)
            ->setToHost('');
        $this->assertSame(
            "ssh -F 'php://temp' 'hostc' 'mysqldump " . ControllerTest::MYSQLDUMP_OPTIONS . " --host='\''database'\'' --user='\''root'\'' --password='\''root'\'' '\''sequelmovie'\'' | (echo '\''CREATE DATABASE IF NOT EXISTS `sequelmovie2`;USE `sequelmovie2`;'\'' && cat) | sed -e '\''s/DEFINER[ ]*=[ ]*[^*]*\*/\*/; s/DEFINER[ ]*=[ ]*[^*]*PROCEDURE/PROCEDURE/; s/DEFINER[ ]*=[ ]*[^*]*FUNCTION/FUNCTION/'\''' | mysql --host='127.0.0.1' --port=2206 --user='root' --password='root'",
            (string)$database->generate(ShellBuilder::new())
        );
    }

    public function testDatabaseCommandWithoutDefinerInstructionsLocallyGenerate(): void
    {
        $sshConfig = new SshConfig();
        $sshConfig->setFile(new SplTempFileObject());
        $database = new DatabaseCommand();
        $fromConnection = (new Database())->setConnectionDetails(DatabaseConnectionDetails::fromUrlString('mysql://root:root@database/sequelmovie'));
        $fromConnection->setRemoveDefinerFromDump(true);
        $toConnection = (new Database())->setConnectionDetails(DatabaseConnectionDetails::fromUrlString('mysql://root:root@127.0.0.1:2206/sequelmovie2'));
        $database->setSshConfig($sshConfig)
            ->setFromDatabase($fromConnection)
            ->setFromHost('')
            ->setToDatabase($toConnection)
            ->setToHost('');
        $this->assertSame(
            "mysqldump " . ControllerTest::MYSQLDUMP_OPTIONS . " --host='database' --user='root' --password='root' 'sequelmovie' | (echo 'CREATE DATABASE IF NOT EXISTS `sequelmovie2`;USE `sequelmovie2`;' && cat) | sed -e 's/DEFINER[ ]*=[ ]*[^*]*\*/\*/; s/DEFINER[ ]*=[ ]*[^*]*PROCEDURE/PROCEDURE/; s/DEFINER[ ]*=[ ]*[^*]*FUNCTION/FUNCTION/' | mysql --host='127.0.0.1' --port=2206 --user='root' --password='root'",
            (string)$database->generate(ShellBuilder::new())
        );
    }

    public function testDatabaseCommandGenerateWithDocker(): void
    {
        $sshConfig = new SshConfig();
        $sshConfig->setFile(new SplTempFileObject());
        $database = new DatabaseCommand();
        $fromConnection = (new Database())
            ->setConnectionDetails(DatabaseConnectionDetails::fromUrlString('mysql://root:root@database/sequelmovie'))
            ->setRemoveDefinerFromDump(false)
            ->executeInDocker(true);
        $toConnection = (new Database())->setConnectionDetails(DatabaseConnectionDetails::fromUrlString('mysql://root:root@127.0.0.1:2206/sequelmovie2'));
        $database->setSshConfig($sshConfig)
            ->setFromDatabase($fromConnection)
            ->setFromHost('hostc')
            ->setToDatabase($toConnection)
            ->setToHost('');
        $dump = $this->getMysqlDumpCommand(['host' => '127.0.0.1', 'user' => 'root', 'password' => 'root', 'database' => 'sequelmovie'], 'sequelmovie2');
        $builder = $this->getSshCommand()
            ->addArgument(ShellBuilder::command('docker')
                ->addArgument('exec')
                ->addShortOption('i')
                ->addArgument('database')
                ->addArgument($dump, false))
            ->addToBuilder()
            ->pipe($this->getMysqlCommand(['host' => '127.0.0.1', 'user' => 'root', 'password' => 'root', 'database' => 'sequelmovie', 'port' => '2206']));
        $generated = $database->generate(ShellBuilder::new());
        $this->assertEquals((string)$builder, (string)$generated);
    }

    public function testDatabaseCommandGenerateWithDockerAndSudo(): void
    {
        $sshConfig = new SshConfig();
        $sshConfig->setFile(new SplTempFileObject());
        $database = new DatabaseCommand();
        $fromConnection = (new Database())
            ->setConnectionDetails(DatabaseConnectionDetails::fromUrlString('mysql://root:root@database/sequelmovie'))
            ->setRemoveDefinerFromDump(false)
            ->executeInDocker(true)
            ->enableSudoForDocker(true);
        $toConnection = (new Database())->setConnectionDetails(DatabaseConnectionDetails::fromUrlString('mysql://root:root@127.0.0.1:2206/sequelmovie2'));
        $database->setSshConfig($sshConfig)
            ->setFromDatabase($fromConnection)
            ->setFromHost('hostc')
            ->setToDatabase($toConnection)
            ->setToHost('');
        $dump = $this->getMysqlDumpCommand(['host' => '127.0.0.1', 'user' => 'root', 'password' => 'root', 'database' => 'sequelmovie'], 'sequelmovie2');
        $builder = $this->getSshCommand()
            ->addArgument(ShellBuilder::command('sudo')
                ->addArgument(
                    ShellBuilder::command('docker')
                        ->addArgument('exec')
                        ->addShortOption('i')
                        ->addArgument('database')
                        ->addArgument($dump, false),
                    false
                ))
            ->addToBuilder()
            ->pipe($this->getMysqlCommand(['host' => '127.0.0.1', 'user' => 'root', 'password' => 'root', 'database' => 'sequelmovie', 'port' => '2206']));
        $generated = $database->generate(ShellBuilder::new());
        $this->assertEquals((string)$builder, (string)$generated);
    }

    public function testDatabaseCommandGenerateWithDockerOnBothSides(): void
    {
        $sshConfig = new SshConfig();
        $sshConfig->setFile(new SplTempFileObject());
        $database = new DatabaseCommand();
        $fromConnection = (new Database())
            ->setConnectionDetails(DatabaseConnectionDetails::fromUrlString('mysql://root:root@database/sequelmovie'))
            ->setRemoveDefinerFromDump(false)
            ->executeInDocker(true);
        $toConnection = (new Database())
            ->setConnectionDetails(DatabaseConnectionDetails::fromUrlString('mysql://root:root@127.0.0.1:2206/sequelmovie2'))
            ->setContainer('test')
            ->executeInDocker(true);
        $database->setSshConfig($sshConfig)
            ->setFromDatabase($fromConnection)
            ->setFromHost('hostc')
            ->setToDatabase($toConnection)
            ->setToHost('');
        $dump = $this->getMysqlDumpCommand(['host' => '127.0.0.1', 'user' => 'root', 'password' => 'root', 'database' => 'sequelmovie'], 'sequelmovie2');
        $builder = $this->getSshCommand()
            ->addArgument(ShellBuilder::command('docker')
                ->addArgument('exec')
                ->addShortOption('i')
                ->addArgument('database')
                ->addArgument($dump, false))
            ->addToBuilder()
            ->pipe(ShellBuilder::command('docker')
                ->addArgument('exec')
                ->addShortOption('i')
                ->addArgument('test')
                ->addArgument(
                    $this->getMysqlCommand(['host' => '127.0.0.1', 'user' => 'root', 'password' => 'root', 'database' => 'sequelmovie']),
                    false
                ));
        $generated = $database->generate(ShellBuilder::new());
        $this->assertEquals((string)$builder, (string)$generated);
    }

    /**
     * @param string $host
     * @return ShellCommand
     * @throws \PHPSu\ShellCommandBuilder\Exception\ShellBuilderException
     */
    private function getSshCommand(string $host = 'hostc'): ShellCommand
    {
        return ShellBuilder::command('ssh')
            ->addShortOption('F')
            ->addArgument('php://temp')
            ->addArgument($host);
    }

    /**
     * @param array<string, string|int> $db
     * @param string $newDb
     * @return ShellBuilder
     * @throws \PHPSu\ShellCommandBuilder\Exception\ShellBuilderException
     */
    private function getMysqlDumpCommand(array $db, string $newDb): ShellBuilder
    {
        return ShellBuilder::command('mysqldump')
            ->addOption('opt')
            ->addOption('skip-comments')
            ->addOption('single-transaction')
            ->addOption('lock-tables', 'false', false, true)
            ->addOption('no-tablespaces')
            ->addOption('complete-insert')
            ->addOption('host', $db['host'], true, true)
            ->addOption('user', $db['user'], true, true)
            ->addOption('password', $db['password'], true, true)
            ->addArgument($db['database'])
            ->addToBuilder()
            ->pipe(
                ShellBuilder::new()
                    ->createGroup()
                    ->createCommand('echo')
                    ->addArgument(sprintf('CREATE DATABASE IF NOT EXISTS `%s`;USE `%s`;', $newDb, $newDb))
                    ->addToBuilder()
                    ->and('cat')
            );
    }

    /**
     * @param array<string, string|int> $db
     * @return \PHPSu\ShellCommandBuilder\ShellCommand
     * @throws \PHPSu\ShellCommandBuilder\Exception\ShellBuilderException
     */
    private function getMysqlCommand(array $db): ShellCommand
    {
        $command = ShellBuilder::command('mysql')
            ->addOption('host', $db['host'], true, true);
        if (isset($db['port'])) {
            $command->addOption('port', $db['port'], false, true);
        }
        return $command->addOption('user', $db['user'], true, true)
            ->addOption('password', $db['password'], true, true);
    }

    public function testDatabaseCommandGzip(): void
    {
        $sshConfig = new SshConfig();
        $sshConfig->setFile(new SplTempFileObject());
        $database = new DatabaseCommand();
        $fromConnection = (new Database())->setConnectionDetails(DatabaseConnectionDetails::fromUrlString('mysql://root:root@database/sequelmovie'));
        $toConnection = (new Database())->setConnectionDetails(DatabaseConnectionDetails::fromUrlString('mysql://root:root@127.0.0.1:2206/sequelmovie2'));
        $database->setSshConfig($sshConfig)
            ->setFromDatabase($fromConnection)
            ->setFromHost('hostc')
            ->setToDatabase($toConnection)
            ->setToHost('')
            ->setCompression(new GzipCompression());

        $this->assertSame(
            "ssh -F 'php://temp' 'hostc' 'mysqldump " . ControllerTest::MYSQLDUMP_OPTIONS . " --host='\''database'\'' --user='\''root'\'' --password='\''root'\'' '\''sequelmovie'\'' | (echo '\''CREATE DATABASE IF NOT EXISTS `sequelmovie2`;USE `sequelmovie2`;'\'' && cat)" . ControllerTest::REMOVE_DEFINER_PART . " | gzip' | gunzip | mysql --host='127.0.0.1' --port=2206 --user='root' --password='root'",
            (string)$database->generate(ShellBuilder::new())
        );
    }

    public function testDatabaseCommandBzip2(): void
    {
        $sshConfig = new SshConfig();
        $sshConfig->setFile(new SplTempFileObject());
        $database = new DatabaseCommand();
        $fromConnection = (new Database())->setConnectionDetails(DatabaseConnectionDetails::fromUrlString('mysql://root:root@database/sequelmovie'));
        $toConnection = (new Database())->setConnectionDetails(DatabaseConnectionDetails::fromUrlString('mysql://root:root@127.0.0.1:2206/sequelmovie2'));
        $database->setSshConfig($sshConfig)
            ->setFromDatabase($fromConnection)
            ->setFromHost('hostc')
            ->setToDatabase($toConnection)
            ->setToHost('')
            ->setCompression(new Bzip2Compression());

        $this->assertSame(
            "ssh -F 'php://temp' 'hostc' 'mysqldump " . ControllerTest::MYSQLDUMP_OPTIONS . " --host='\''database'\'' --user='\''root'\'' --password='\''root'\'' '\''sequelmovie'\'' | (echo '\''CREATE DATABASE IF NOT EXISTS `sequelmovie2`;USE `sequelmovie2`;'\'' && cat)" . ControllerTest::REMOVE_DEFINER_PART . " | bzip2' | bunzip2 | mysql --host='127.0.0.1' --port=2206 --user='root' --password='root'",
            (string)$database->generate(ShellBuilder::new())
        );
    }

    public function testDatabaseCommandQuiet(): void
    {
        $sshConfig = new SshConfig();
        $sshConfig->setFile(new SplTempFileObject());
        $database = new DatabaseCommand();
        $fromConnection = (new Database())->setConnectionDetails(DatabaseConnectionDetails::fromUrlString('mysql://root:root@database/sequelmovie'));
        $toConnection = (new Database())->setConnectionDetails(DatabaseConnectionDetails::fromUrlString('mysql://root:root@127.0.0.1:2206/sequelmovie2'));
        $database->setSshConfig($sshConfig)
            ->setFromDatabase($fromConnection)
            ->setFromHost('hostc')
            ->setToDatabase($toConnection)
            ->setToHost('')
            ->setVerbosity(OutputInterface::VERBOSITY_QUIET);

        $this->assertSame(
            "ssh -q -F 'php://temp' 'hostc' 'mysqldump -q " . ControllerTest::MYSQLDUMP_OPTIONS . " --host='\''database'\'' --user='\''root'\'' --password='\''root'\'' '\''sequelmovie'\'' | (echo '\''CREATE DATABASE IF NOT EXISTS `sequelmovie2`;USE `sequelmovie2`;'\'' && cat)" . ControllerTest::REMOVE_DEFINER_PART . "' | mysql --host='127.0.0.1' --port=2206 --user='root' --password='root'",
            (string)$database->generate(ShellBuilder::new())
        );
    }

    public function testDatabaseCommandVerbose(): void
    {
        $sshConfig = new SshConfig();
        $sshConfig->setFile(new SplTempFileObject());
        $database = new DatabaseCommand();
        $fromConnection = (new Database())->setConnectionDetails(DatabaseConnectionDetails::fromUrlString('mysql://root:root@database/sequelmovie'));
        $toConnection = (new Database())->setConnectionDetails(DatabaseConnectionDetails::fromUrlString('mysql://root:root@127.0.0.1:2206/sequelmovie2'));
        $database->setSshConfig($sshConfig)
            ->setFromDatabase($fromConnection)
            ->setFromHost('hostc')
            ->setToDatabase($toConnection)
            ->setToHost('')
            ->setVerbosity(OutputInterface::VERBOSITY_VERBOSE);

        $this->assertSame(
            "ssh -v -F 'php://temp' 'hostc' 'mysqldump -v " . ControllerTest::MYSQLDUMP_OPTIONS . " --host='\''database'\'' --user='\''root'\'' --password='\''root'\'' '\''sequelmovie'\'' | (echo '\''CREATE DATABASE IF NOT EXISTS `sequelmovie2`;USE `sequelmovie2`;'\'' && cat)" . ControllerTest::REMOVE_DEFINER_PART . "' | mysql --host='127.0.0.1' --port=2206 --user='root' --password='root'",
            (string)$database->generate(ShellBuilder::new())
        );
    }

    public function testDatabaseCommandVeryVerbose(): void
    {
        $sshConfig = new SshConfig();
        $sshConfig->setFile(new SplTempFileObject());
        $database = new DatabaseCommand();
        $fromConnection = (new Database())->setConnectionDetails(DatabaseConnectionDetails::fromUrlString('mysql://root:root@database/sequelmovie'));
        $toConnection = (new Database())->setConnectionDetails(DatabaseConnectionDetails::fromUrlString('mysql://root:root@127.0.0.1:2206/sequelmovie2'));
        $database->setSshConfig($sshConfig)
            ->setFromDatabase($fromConnection)
            ->setFromHost('hostc')
            ->setToDatabase($toConnection)
            ->setToHost('')
            ->setVerbosity(OutputInterface::VERBOSITY_VERY_VERBOSE);

        $this->assertSame(
            "ssh -vv -F 'php://temp' 'hostc' 'mysqldump -vv " . ControllerTest::MYSQLDUMP_OPTIONS . " --host='\''database'\'' --user='\''root'\'' --password='\''root'\'' '\''sequelmovie'\'' | (echo '\''CREATE DATABASE IF NOT EXISTS `sequelmovie2`;USE `sequelmovie2`;'\'' && cat)" . ControllerTest::REMOVE_DEFINER_PART . "' | mysql --host='127.0.0.1' --port=2206 --user='root' --password='root'",
            (string)$database->generate(ShellBuilder::new())
        );
    }

    public function testDatabaseCommandDebug(): void
    {
        $sshConfig = new SshConfig();
        $sshConfig->setFile(new SplTempFileObject());
        $database = new DatabaseCommand();
        $fromConnection = (new Database())->setConnectionDetails(DatabaseConnectionDetails::fromUrlString('mysql://root:root@database/sequelmovie'));
        $toConnection = (new Database())->setConnectionDetails(DatabaseConnectionDetails::fromUrlString('mysql://root:root@127.0.0.1:2206/sequelmovie2'));
        $database->setSshConfig($sshConfig)
            ->setFromDatabase($fromConnection)
            ->setFromHost('hostc')
            ->setToDatabase($toConnection)
            ->setToHost('')
            ->setVerbosity(OutputInterface::VERBOSITY_DEBUG);

        $this->assertSame(
            "ssh -vvv -F 'php://temp' 'hostc' 'mysqldump -vvv " . ControllerTest::MYSQLDUMP_OPTIONS . " --host='\''database'\'' --user='\''root'\'' --password='\''root'\'' '\''sequelmovie'\'' | (echo '\''CREATE DATABASE IF NOT EXISTS `sequelmovie2`;USE `sequelmovie2`;'\'' && cat)" . ControllerTest::REMOVE_DEFINER_PART . "' | mysql --host='127.0.0.1' --port=2206 --user='root' --password='root'",
            (string)$database->generate(ShellBuilder::new())
        );
    }

    public function testDatabaseCommandPasswordWithSpecialCharacters(): void
    {
        $sshConfig = new SshConfig();
        $sshConfig->setFile(new SplTempFileObject());
        $database = new DatabaseCommand();
        $fromConnection = (new Database())->setConnectionDetails(DatabaseConnectionDetails::fromDetails('sequelmovie', 'root', 'root#password\'"_!', 'database'));
        $toConnection = (new Database())->setConnectionDetails(DatabaseConnectionDetails::fromUrlString('mysql://root:root@127.0.0.1:2206/sequelmovie2'));
        $database->setSshConfig($sshConfig)
            ->setFromDatabase($fromConnection)
            ->setFromHost('hostc')
            ->setToDatabase($toConnection)
            ->setToHost('');
        $this->assertSame(
            "ssh -F 'php://temp' 'hostc' 'mysqldump " . ControllerTest::MYSQLDUMP_OPTIONS . " --host='\''database'\'' --user='\''root'\'' --password='\''root#password'\''\'\'''\''\"_!'\'' '\''sequelmovie'\'' | (echo '\''CREATE DATABASE IF NOT EXISTS `sequelmovie2`;USE `sequelmovie2`;'\'' && cat)" . ControllerTest::REMOVE_DEFINER_PART . "' | mysql --host='127.0.0.1' --port=2206 --user='root' --password='root'",
            (string)$database->generate(ShellBuilder::new())
        );
    }

    public function testDatabaseCommandGetter(): void
    {
        $sshConfig = new SshConfig();
        $sshConfig->setFile(new SplTempFileObject());
        $database = new DatabaseCommand();
        $gzipCompression = new GzipCompression();
        $fromConnection = (new Database())->setConnectionDetails(DatabaseConnectionDetails::fromUrlString('mysql://root:root@database/sequelmovie'));
        $toConnection = (new Database())->setConnectionDetails(DatabaseConnectionDetails::fromUrlString('mysql://root:root@127.0.0.1:2206/sequelmovie2'));
        $database->setName('databaseName')
            ->setSshConfig($sshConfig)
            ->setFromDatabase($fromConnection)
            ->setFromHost('hostc')
            ->setToDatabase($toConnection)
            ->setToHost('')
            ->setVerbosity(OutputInterface::VERBOSITY_DEBUG)
            ->setExcludes(['exclude1', 'exclude2'])
            ->setCompression($gzipCompression);

        $this->assertSame('databaseName', $database->getName());
        $this->assertSame($sshConfig, $database->getSshConfig());
        $this->assertSame(['exclude1', 'exclude2'], $database->getExcludes());
        $this->assertSame($fromConnection, $database->getFromDatabase());
        $this->assertSame('hostc', $database->getFromHost());
        $this->assertSame($toConnection, $database->getToDatabase());
        $this->assertSame('', $database->getToHost());
        $this->assertSame(OutputInterface::VERBOSITY_DEBUG, $database->getVerbosity());
        $this->assertSame($gzipCompression, $database->getCompression());
    }
}
