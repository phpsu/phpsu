<?php

declare(strict_types=1);

namespace PHPSu\Tests\Command;

use PHPSu\ShellCommandBuilder\Exception\ShellBuilderException;
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
            "set -o pipefail && ssh -F 'php://temp' 'hostc' 'set -o pipefail && mysqldump " . ControllerTest::MYSQLDUMP_OPTIONS . " --host='\''database'\'' --user='\''root'\'' --password='\''root'\'' '\''sequelmovie'\'' | (echo '\''CREATE DATABASE IF NOT EXISTS `sequelmovie2`;USE `sequelmovie2`;'\'' && cat)" . ControllerTest::MYSQL_DUMP_MODIFICATION_PART . "' | mysql --host='127.0.0.1' --port=2206 --user='root' --password='root'",
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
            "set -o pipefail && ssh -F 'php://temp' 'hostc' 'set -o pipefail && mysqldump " . ControllerTest::MYSQLDUMP_OPTIONS . " --host='\''database'\'' --user='\''root'\'' --password='\''root'\'' '\''sequelmovie'\'' | (echo '\''CREATE DATABASE IF NOT EXISTS `sequelmovie2`;USE `sequelmovie2`;'\'' && cat)" . ControllerTest::MYSQL_DUMP_MODIFICATION_PART . "' | mysql --host='127.0.0.1' --port=2206 --user='root' --password='root'",
            (string)$database->generate(ShellBuilder::new())
        );
    }

    public function testDatabaseCommandWithoutDefinerInstructionsLocallyGenerate(): void
    {
        $sshConfig = new SshConfig();
        $sshConfig->setFile(new SplTempFileObject());

        $database = new DatabaseCommand();
        $fromConnection = (new Database())->setConnectionDetails(DatabaseConnectionDetails::fromUrlString('mysql://root:root@database/sequelmovie'));
        $fromConnection
            ->setRemoveDefinerFromDump(true)
            ->setAllowSandboxMode(true);

        $toConnection = (new Database())->setConnectionDetails(DatabaseConnectionDetails::fromUrlString('mysql://root:root@127.0.0.1:2206/sequelmovie2'));
        $database->setSshConfig($sshConfig)
            ->setFromDatabase($fromConnection)
            ->setFromHost('')
            ->setToDatabase($toConnection)
            ->setToHost('');
        $this->assertSame(
            "set -o pipefail && mysqldump " . ControllerTest::MYSQLDUMP_OPTIONS . " --host='database' --user='root' --password='root' 'sequelmovie' | (echo 'CREATE DATABASE IF NOT EXISTS `sequelmovie2`;USE `sequelmovie2`;' && cat) | sed -e 's/DEFINER[ ]*=[ ]*[^*]*\*/\*/; s/DEFINER[ ]*=[ ]*[^*]*PROCEDURE/PROCEDURE/; s/DEFINER[ ]*=[ ]*[^*]*FUNCTION/FUNCTION/' | mysql --host='127.0.0.1' --port=2206 --user='root' --password='root'",
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
            ->setAllowSandboxMode(true)
            ->executeInDocker(true);
        $toConnection = (new Database())->setConnectionDetails(DatabaseConnectionDetails::fromUrlString('mysql://root:root@127.0.0.1:2206/sequelmovie2'));
        $database->setSshConfig($sshConfig)
            ->setFromDatabase($fromConnection)
            ->setFromHost('hostc')
            ->setToDatabase($toConnection)
            ->setToHost('');
        $generated = $database->generate(ShellBuilder::new());
        $this->assertEquals("set -o pipefail && ssh -F 'php://temp' 'hostc' 'set -o pipefail && docker '\''exec'\'' -i '\''database'\'' mysqldump --opt --skip-comments --single-transaction --lock-tables=false --no-tablespaces --complete-insert --host='\''127.0.0.1'\'' --user='\''root'\'' --password='\''root'\'' '\''sequelmovie'\'' | (echo '\''CREATE DATABASE IF NOT EXISTS `sequelmovie2`;USE `sequelmovie2`;'\'' && cat)' | mysql --host='127.0.0.1' --port=2206 --user='root' --password='root'", (string)$generated);
    }

    public function testDatabaseCommandGenerateWithDockerAndSudo(): void
    {
        $sshConfig = new SshConfig();
        $sshConfig->setFile(new SplTempFileObject());

        $database = new DatabaseCommand();
        $fromConnection = (new Database())
            ->setConnectionDetails(DatabaseConnectionDetails::fromUrlString('mysql://root:root@database/sequelmovie'))
            ->setRemoveDefinerFromDump(false)
            ->setAllowSandboxMode(true)
            ->executeInDocker(true)
            ->enableSudoForDocker(true);
        $toConnection = (new Database())->setConnectionDetails(DatabaseConnectionDetails::fromUrlString('mysql://root:root@127.0.0.1:2206/sequelmovie2'));
        $database->setSshConfig($sshConfig)
            ->setFromDatabase($fromConnection)
            ->setFromHost('hostc')
            ->setToDatabase($toConnection)
            ->setToHost('');
        $generated = $database->generate(ShellBuilder::new());
        $this->assertEquals("set -o pipefail && ssh -F 'php://temp' 'hostc' 'set -o pipefail && sudo docker '\''exec'\'' -i '\''database'\'' mysqldump --opt --skip-comments --single-transaction --lock-tables=false --no-tablespaces --complete-insert --host='\''127.0.0.1'\'' --user='\''root'\'' --password='\''root'\'' '\''sequelmovie'\'' | (echo '\''CREATE DATABASE IF NOT EXISTS `sequelmovie2`;USE `sequelmovie2`;'\'' && cat)' | mysql --host='127.0.0.1' --port=2206 --user='root' --password='root'", (string)$generated);
    }

    public function testDatabaseCommandGenerateWithDockerOnBothSides(): void
    {
        $sshConfig = new SshConfig();
        $sshConfig->setFile(new SplTempFileObject());

        $database = new DatabaseCommand();
        $fromConnection = (new Database())
            ->setConnectionDetails(DatabaseConnectionDetails::fromUrlString('mysql://root:root@database/sequelmovie'))
            ->setRemoveDefinerFromDump(false)
            ->setAllowSandboxMode(true)
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
        $generated = $database->generate(ShellBuilder::new());
        $this->assertEquals("set -o pipefail && ssh -F 'php://temp' 'hostc' 'set -o pipefail && docker '\''exec'\'' -i '\''database'\'' mysqldump --opt --skip-comments --single-transaction --lock-tables=false --no-tablespaces --complete-insert --host='\''127.0.0.1'\'' --user='\''root'\'' --password='\''root'\'' '\''sequelmovie'\'' | (echo '\''CREATE DATABASE IF NOT EXISTS `sequelmovie2`;USE `sequelmovie2`;'\'' && cat)' | docker 'exec' -i 'test' mysql --host='127.0.0.1' --user='root' --password='root'", (string)$generated);
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
            "set -o pipefail && ssh -F 'php://temp' 'hostc' 'set -o pipefail && mysqldump " . ControllerTest::MYSQLDUMP_OPTIONS . " --host='\''database'\'' --user='\''root'\'' --password='\''root'\'' '\''sequelmovie'\'' | (echo '\''CREATE DATABASE IF NOT EXISTS `sequelmovie2`;USE `sequelmovie2`;'\'' && cat)" . ControllerTest::MYSQL_DUMP_MODIFICATION_PART . " | gzip' | gunzip | mysql --host='127.0.0.1' --port=2206 --user='root' --password='root'",
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
            "set -o pipefail && ssh -F 'php://temp' 'hostc' 'set -o pipefail && mysqldump " . ControllerTest::MYSQLDUMP_OPTIONS . " --host='\''database'\'' --user='\''root'\'' --password='\''root'\'' '\''sequelmovie'\'' | (echo '\''CREATE DATABASE IF NOT EXISTS `sequelmovie2`;USE `sequelmovie2`;'\'' && cat)" . ControllerTest::MYSQL_DUMP_MODIFICATION_PART . " | bzip2' | bunzip2 | mysql --host='127.0.0.1' --port=2206 --user='root' --password='root'",
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
            "set -o pipefail && ssh -q -F 'php://temp' 'hostc' 'set -o pipefail && mysqldump -q " . ControllerTest::MYSQLDUMP_OPTIONS . " --host='\''database'\'' --user='\''root'\'' --password='\''root'\'' '\''sequelmovie'\'' | (echo '\''CREATE DATABASE IF NOT EXISTS `sequelmovie2`;USE `sequelmovie2`;'\'' && cat)" . ControllerTest::MYSQL_DUMP_MODIFICATION_PART . "' | mysql --host='127.0.0.1' --port=2206 --user='root' --password='root'",
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
            "set -o pipefail && ssh -v -F 'php://temp' 'hostc' 'set -o pipefail && mysqldump -v " . ControllerTest::MYSQLDUMP_OPTIONS . " --host='\''database'\'' --user='\''root'\'' --password='\''root'\'' '\''sequelmovie'\'' | (echo '\''CREATE DATABASE IF NOT EXISTS `sequelmovie2`;USE `sequelmovie2`;'\'' && cat)" . ControllerTest::MYSQL_DUMP_MODIFICATION_PART . "' | mysql --host='127.0.0.1' --port=2206 --user='root' --password='root'",
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
            "set -o pipefail && ssh -vv -F 'php://temp' 'hostc' 'set -o pipefail && mysqldump -vv " . ControllerTest::MYSQLDUMP_OPTIONS . " --host='\''database'\'' --user='\''root'\'' --password='\''root'\'' '\''sequelmovie'\'' | (echo '\''CREATE DATABASE IF NOT EXISTS `sequelmovie2`;USE `sequelmovie2`;'\'' && cat)" . ControllerTest::MYSQL_DUMP_MODIFICATION_PART . "' | mysql --host='127.0.0.1' --port=2206 --user='root' --password='root'",
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
            "set -o pipefail && ssh -vvv -F 'php://temp' 'hostc' 'set -o pipefail && mysqldump -vvv " . ControllerTest::MYSQLDUMP_OPTIONS . " --host='\''database'\'' --user='\''root'\'' --password='\''root'\'' '\''sequelmovie'\'' | (echo '\''CREATE DATABASE IF NOT EXISTS `sequelmovie2`;USE `sequelmovie2`;'\'' && cat)" . ControllerTest::MYSQL_DUMP_MODIFICATION_PART . "' | mysql --host='127.0.0.1' --port=2206 --user='root' --password='root'",
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
            "set -o pipefail && ssh -F 'php://temp' 'hostc' 'set -o pipefail && mysqldump " . ControllerTest::MYSQLDUMP_OPTIONS . " --host='\''database'\'' --user='\''root'\'' --password='\''root#password'\''\'\'''\''\"_!'\'' '\''sequelmovie'\'' | (echo '\''CREATE DATABASE IF NOT EXISTS `sequelmovie2`;USE `sequelmovie2`;'\'' && cat)" . ControllerTest::MYSQL_DUMP_MODIFICATION_PART . "' | mysql --host='127.0.0.1' --port=2206 --user='root' --password='root'",
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
