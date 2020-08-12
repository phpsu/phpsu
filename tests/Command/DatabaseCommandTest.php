<?php

declare(strict_types=1);

namespace PHPSu\Tests\Command;

use PHPSu\Command\DatabaseCommand;
use PHPSu\Config\Compression\Bzip2Compression;
use PHPSu\Config\Compression\GzipCompression;
use PHPSu\Config\DatabaseConnectionDetails;
use PHPSu\Config\SshConfig;
use PHPSu\ShellCommandBuilder\ShellBuilder;
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
        $database->setSshConfig($sshConfig)
            ->setFromConnectionDetails(DatabaseConnectionDetails::fromUrlString('mysql://root:root@database/sequelmovie'))
            ->setFromHost('hostc')
            ->setToConnectionDetails(DatabaseConnectionDetails::fromUrlString('mysql://root:root@127.0.0.1:2206/sequelmovie2'))
            ->setToHost('');
        $this->assertSame(
            "ssh -F 'php://temp' 'hostc' 'mysqldump --opt --skip-comments --single-transaction --lock-tables=false --host='\''database'\'' --user='\''root'\'' --password='\''root'\'' '\''sequelmovie'\'' | (echo '\''CREATE DATABASE IF NOT EXISTS `sequelmovie2`;USE `sequelmovie2`;'\'' && cat)' | mysql --host='127.0.0.1' --port=2206 --user='root' --password='root'",
            (string)$database->generate(ShellBuilder::new())
        );
    }

    public function testDatabaseCommandGzip(): void
    {
        $sshConfig = new SshConfig();
        $sshConfig->setFile(new SplTempFileObject());
        $database = new DatabaseCommand();
        $database->setSshConfig($sshConfig)
            ->setFromConnectionDetails(DatabaseConnectionDetails::fromUrlString('mysql://root:root@database/sequelmovie'))
            ->setFromHost('hostc')
            ->setToConnectionDetails(DatabaseConnectionDetails::fromUrlString('mysql://root:root@127.0.0.1:2206/sequelmovie2'))
            ->setToHost('')
            ->setCompression(new GzipCompression());

        $this->assertSame(
            "ssh -F 'php://temp' 'hostc' 'mysqldump --opt --skip-comments --single-transaction --lock-tables=false --host='\''database'\'' --user='\''root'\'' --password='\''root'\'' '\''sequelmovie'\'' | (echo '\''CREATE DATABASE IF NOT EXISTS `sequelmovie2`;USE `sequelmovie2`;'\'' && cat) | gzip' | gunzip | mysql --host='127.0.0.1' --port=2206 --user='root' --password='root'",
            (string)$database->generate(ShellBuilder::new())
        );
    }

    public function testDatabaseCommandBzip2(): void
    {
        $sshConfig = new SshConfig();
        $sshConfig->setFile(new SplTempFileObject());
        $database = new DatabaseCommand();
        $database->setSshConfig($sshConfig)
            ->setFromConnectionDetails(DatabaseConnectionDetails::fromUrlString('mysql://root:root@database/sequelmovie'))
            ->setFromHost('hostc')
            ->setToConnectionDetails(DatabaseConnectionDetails::fromUrlString('mysql://root:root@127.0.0.1:2206/sequelmovie2'))
            ->setToHost('')
            ->setCompression(new Bzip2Compression());

        $this->assertSame(
            "ssh -F 'php://temp' 'hostc' 'mysqldump --opt --skip-comments --single-transaction --lock-tables=false --host='\''database'\'' --user='\''root'\'' --password='\''root'\'' '\''sequelmovie'\'' | (echo '\''CREATE DATABASE IF NOT EXISTS `sequelmovie2`;USE `sequelmovie2`;'\'' && cat) | bzip2' | bunzip2 | mysql --host='127.0.0.1' --port=2206 --user='root' --password='root'",
            (string)$database->generate(ShellBuilder::new())
        );
    }

    public function testDatabaseCommandQuiet(): void
    {
        $sshConfig = new SshConfig();
        $sshConfig->setFile(new SplTempFileObject());
        $database = new DatabaseCommand();
        $database->setSshConfig($sshConfig)
            ->setFromConnectionDetails(DatabaseConnectionDetails::fromUrlString('mysql://root:root@database/sequelmovie'))
            ->setFromHost('hostc')
            ->setToConnectionDetails(DatabaseConnectionDetails::fromUrlString('mysql://root:root@127.0.0.1:2206/sequelmovie2'))
            ->setToHost('')
            ->setVerbosity(OutputInterface::VERBOSITY_QUIET);

        $this->assertSame(
            "ssh -q -F 'php://temp' 'hostc' 'mysqldump -q --opt --skip-comments --single-transaction --lock-tables=false --host='\''database'\'' --user='\''root'\'' --password='\''root'\'' '\''sequelmovie'\'' | (echo '\''CREATE DATABASE IF NOT EXISTS `sequelmovie2`;USE `sequelmovie2`;'\'' && cat)' | mysql --host='127.0.0.1' --port=2206 --user='root' --password='root'",
            (string)$database->generate(ShellBuilder::new())
        );
    }

    public function testDatabaseCommandVerbose(): void
    {
        $sshConfig = new SshConfig();
        $sshConfig->setFile(new SplTempFileObject());
        $database = new DatabaseCommand();
        $database->setSshConfig($sshConfig)
            ->setFromConnectionDetails(DatabaseConnectionDetails::fromUrlString('mysql://root:root@database/sequelmovie'))
            ->setFromHost('hostc')
            ->setToConnectionDetails(DatabaseConnectionDetails::fromUrlString('mysql://root:root@127.0.0.1:2206/sequelmovie2'))
            ->setToHost('')
            ->setVerbosity(OutputInterface::VERBOSITY_VERBOSE);

        $this->assertSame(
            "ssh -v -F 'php://temp' 'hostc' 'mysqldump -v --opt --skip-comments --single-transaction --lock-tables=false --host='\''database'\'' --user='\''root'\'' --password='\''root'\'' '\''sequelmovie'\'' | (echo '\''CREATE DATABASE IF NOT EXISTS `sequelmovie2`;USE `sequelmovie2`;'\'' && cat)' | mysql --host='127.0.0.1' --port=2206 --user='root' --password='root'",
            (string)$database->generate(ShellBuilder::new())
        );
    }

    public function testDatabaseCommandVeryVerbose(): void
    {
        $sshConfig = new SshConfig();
        $sshConfig->setFile(new SplTempFileObject());
        $database = new DatabaseCommand();
        $database->setSshConfig($sshConfig)
            ->setFromConnectionDetails(DatabaseConnectionDetails::fromUrlString('mysql://root:root@database/sequelmovie'))
            ->setFromHost('hostc')
            ->setToConnectionDetails(DatabaseConnectionDetails::fromUrlString('mysql://root:root@127.0.0.1:2206/sequelmovie2'))
            ->setToHost('')
            ->setVerbosity(OutputInterface::VERBOSITY_VERY_VERBOSE);

        $this->assertSame(
            "ssh -vv -F 'php://temp' 'hostc' 'mysqldump -vv --opt --skip-comments --single-transaction --lock-tables=false --host='\''database'\'' --user='\''root'\'' --password='\''root'\'' '\''sequelmovie'\'' | (echo '\''CREATE DATABASE IF NOT EXISTS `sequelmovie2`;USE `sequelmovie2`;'\'' && cat)' | mysql --host='127.0.0.1' --port=2206 --user='root' --password='root'",
            (string)$database->generate(ShellBuilder::new())
        );
    }

    public function testDatabaseCommandDebug(): void
    {
        $sshConfig = new SshConfig();
        $sshConfig->setFile(new SplTempFileObject());
        $database = new DatabaseCommand();
        $database->setSshConfig($sshConfig)
            ->setFromConnectionDetails(DatabaseConnectionDetails::fromUrlString('mysql://root:root@database/sequelmovie'))
            ->setFromHost('hostc')
            ->setToConnectionDetails(DatabaseConnectionDetails::fromUrlString('mysql://root:root@127.0.0.1:2206/sequelmovie2'))
            ->setToHost('')
            ->setVerbosity(OutputInterface::VERBOSITY_DEBUG);

        $this->assertSame(
            "ssh -vvv -F 'php://temp' 'hostc' 'mysqldump -vvv --opt --skip-comments --single-transaction --lock-tables=false --host='\''database'\'' --user='\''root'\'' --password='\''root'\'' '\''sequelmovie'\'' | (echo '\''CREATE DATABASE IF NOT EXISTS `sequelmovie2`;USE `sequelmovie2`;'\'' && cat)' | mysql --host='127.0.0.1' --port=2206 --user='root' --password='root'",
            (string)$database->generate(ShellBuilder::new())
        );
    }
    public function testDatabaseCommandPasswordWithSpecialCharacters(): void
    {
        $sshConfig = new SshConfig();
        $sshConfig->setFile(new SplTempFileObject());
        $database = new DatabaseCommand();
        $database->setSshConfig($sshConfig)
            ->setFromConnectionDetails(DatabaseConnectionDetails::fromDetails('sequelmovie', 'root', 'root#password\'"_!', 'database'))
            ->setFromHost('hostc')
            ->setToConnectionDetails(DatabaseConnectionDetails::fromUrlString('mysql://root:root@127.0.0.1:2206/sequelmovie2'))
            ->setToHost('');
        $this->assertSame(
            "ssh -F 'php://temp' 'hostc' 'mysqldump --opt --skip-comments --single-transaction --lock-tables=false --host='\''database'\'' --user='\''root'\'' --password='\''root#password'\''\'\'''\''\"_!'\'' '\''sequelmovie'\'' | (echo '\''CREATE DATABASE IF NOT EXISTS `sequelmovie2`;USE `sequelmovie2`;'\'' && cat)' | mysql --host='127.0.0.1' --port=2206 --user='root' --password='root'",
            (string)$database->generate(ShellBuilder::new())
        );
    }

    public function testDatabaseCommandGetter(): void
    {
        $sshConfig = new SshConfig();
        $sshConfig->setFile(new SplTempFileObject());
        $database = new DatabaseCommand();
        $gzipCompression = new GzipCompression();
        $fromConnectionDetails = DatabaseConnectionDetails::fromUrlString('mysql://root:root@database/sequelmovie');
        $toConnectionDetails = DatabaseConnectionDetails::fromUrlString('mysql://root:root@127.0.0.1:2206/sequelmovie2');
        $database->setName('databaseName')
            ->setSshConfig($sshConfig)
            ->setFromConnectionDetails($fromConnectionDetails)
            ->setFromHost('hostc')
            ->setToConnectionDetails($toConnectionDetails)
            ->setToHost('')
            ->setVerbosity(OutputInterface::VERBOSITY_DEBUG)
            ->setExcludes(['exclude1', 'exclude2'])
            ->setCompression($gzipCompression);

        $this->assertSame('databaseName', $database->getName());
        $this->assertSame($sshConfig, $database->getSshConfig());
        $this->assertSame(['exclude1', 'exclude2'], $database->getExcludes());
        $this->assertSame($fromConnectionDetails, $database->getFromConnectionDetails());
        $this->assertSame('hostc', $database->getFromHost());
        $this->assertSame($toConnectionDetails, $database->getToConnectionDetails());
        $this->assertSame('', $database->getToHost());
        $this->assertSame(OutputInterface::VERBOSITY_DEBUG, $database->getVerbosity());
        $this->assertSame($gzipCompression, $database->getCompression());
    }
}
