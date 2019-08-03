<?php
declare(strict_types=1);

namespace PHPSu\Tests\Command;

use PHPSu\Command\DatabaseCommand;
use PHPSu\Config\Compression\Bzip2Compression;
use PHPSu\Config\Compression\GzipCompression;
use PHPSu\Config\SshConfig;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\OutputInterface;

final class DatabaseCommandTest extends TestCase
{
    public function testDatabaseCommandGenerate()
    {
        $sshConfig = new SshConfig();
        $sshConfig->setFile(new \SplTempFileObject());
        $database = new DatabaseCommand();
        $database->setSshConfig($sshConfig)
            ->setFromUrl('mysql://root:root@database/sequelmovie')
            ->setFromHost('hostc')
            ->setToUrl('mysql://root:root@127.0.0.1:2206/sequelmovie2')
            ->setToHost('');
        $this->assertSame("ssh -F 'php://temp' 'hostc' 'mysqldump --opt --skip-comments -h'\''database'\'' -u'\''root'\'' -p'\''root'\'' '\''sequelmovie'\'' | (echo '\''CREATE DATABASE IF NOT EXISTS `sequelmovie2`;USE `sequelmovie2`;'\'' && cat)' | mysql -h'127.0.0.1' -P2206 -u'root' -p'root'", $database->generate());
    }

    public function testDatabaseCommandGzip()
    {
        $sshConfig = new SshConfig();
        $sshConfig->setFile(new \SplTempFileObject());
        $database = new DatabaseCommand();
        $database->setSshConfig($sshConfig)
            ->setFromUrl('mysql://root:root@database/sequelmovie')
            ->setFromHost('hostc')
            ->setToUrl('mysql://root:root@127.0.0.1:2206/sequelmovie2')
            ->setToHost('')
            ->setCompression(new GzipCompression());

        $this->assertSame("ssh -F 'php://temp' 'hostc' 'mysqldump --opt --skip-comments -h'\''database'\'' -u'\''root'\'' -p'\''root'\'' '\''sequelmovie'\'' | (echo '\''CREATE DATABASE IF NOT EXISTS `sequelmovie2`;USE `sequelmovie2`;'\'' && cat) | gzip' | gunzip | mysql -h'127.0.0.1' -P2206 -u'root' -p'root'", $database->generate());
    }

    public function testDatabaseCommandBzip2()
    {
        $sshConfig = new SshConfig();
        $sshConfig->setFile(new \SplTempFileObject());
        $database = new DatabaseCommand();
        $database->setSshConfig($sshConfig)
            ->setFromUrl('mysql://root:root@database/sequelmovie')
            ->setFromHost('hostc')
            ->setToUrl('mysql://root:root@127.0.0.1:2206/sequelmovie2')
            ->setToHost('')
            ->setCompression(new Bzip2Compression());

        $this->assertSame("ssh -F 'php://temp' 'hostc' 'mysqldump --opt --skip-comments -h'\''database'\'' -u'\''root'\'' -p'\''root'\'' '\''sequelmovie'\'' | (echo '\''CREATE DATABASE IF NOT EXISTS `sequelmovie2`;USE `sequelmovie2`;'\'' && cat) | bzip2' | bunzip2 | mysql -h'127.0.0.1' -P2206 -u'root' -p'root'", $database->generate());
    }

    public function testDatabaseCommandQuiet()
    {
        $sshConfig = new SshConfig();
        $sshConfig->setFile(new \SplTempFileObject());
        $database = new DatabaseCommand();
        $database->setSshConfig($sshConfig)
            ->setFromUrl('mysql://root:root@database/sequelmovie')
            ->setFromHost('hostc')
            ->setToUrl('mysql://root:root@127.0.0.1:2206/sequelmovie2')
            ->setToHost('')
            ->setVerbosity(OutputInterface::VERBOSITY_QUIET);

        $this->assertSame("ssh -q -F 'php://temp' 'hostc' 'mysqldump -q --opt --skip-comments -h'\''database'\'' -u'\''root'\'' -p'\''root'\'' '\''sequelmovie'\'' | (echo '\''CREATE DATABASE IF NOT EXISTS `sequelmovie2`;USE `sequelmovie2`;'\'' && cat)' | mysql -h'127.0.0.1' -P2206 -u'root' -p'root'", $database->generate());
    }

    public function testDatabaseCommandVerbose()
    {
        $sshConfig = new SshConfig();
        $sshConfig->setFile(new \SplTempFileObject());
        $database = new DatabaseCommand();
        $database->setSshConfig($sshConfig)
            ->setFromUrl('mysql://root:root@database/sequelmovie')
            ->setFromHost('hostc')
            ->setToUrl('mysql://root:root@127.0.0.1:2206/sequelmovie2')
            ->setToHost('')
            ->setVerbosity(OutputInterface::VERBOSITY_VERBOSE);

        $this->assertSame("ssh -v -F 'php://temp' 'hostc' 'mysqldump -v --opt --skip-comments -h'\''database'\'' -u'\''root'\'' -p'\''root'\'' '\''sequelmovie'\'' | (echo '\''CREATE DATABASE IF NOT EXISTS `sequelmovie2`;USE `sequelmovie2`;'\'' && cat)' | mysql -h'127.0.0.1' -P2206 -u'root' -p'root'", $database->generate());
    }

    public function testDatabaseCommandVeryVerbose()
    {
        $sshConfig = new SshConfig();
        $sshConfig->setFile(new \SplTempFileObject());
        $database = new DatabaseCommand();
        $database->setSshConfig($sshConfig)
            ->setFromUrl('mysql://root:root@database/sequelmovie')
            ->setFromHost('hostc')
            ->setToUrl('mysql://root:root@127.0.0.1:2206/sequelmovie2')
            ->setToHost('')
            ->setVerbosity(OutputInterface::VERBOSITY_VERY_VERBOSE);

        $this->assertSame("ssh -vv -F 'php://temp' 'hostc' 'mysqldump -vv --opt --skip-comments -h'\''database'\'' -u'\''root'\'' -p'\''root'\'' '\''sequelmovie'\'' | (echo '\''CREATE DATABASE IF NOT EXISTS `sequelmovie2`;USE `sequelmovie2`;'\'' && cat)' | mysql -h'127.0.0.1' -P2206 -u'root' -p'root'", $database->generate());
    }

    public function testDatabaseCommandDebug()
    {
        $sshConfig = new SshConfig();
        $sshConfig->setFile(new \SplTempFileObject());
        $database = new DatabaseCommand();
        $database->setSshConfig($sshConfig)
            ->setFromUrl('mysql://root:root@database/sequelmovie')
            ->setFromHost('hostc')
            ->setToUrl('mysql://root:root@127.0.0.1:2206/sequelmovie2')
            ->setToHost('')
            ->setVerbosity(OutputInterface::VERBOSITY_DEBUG);

        $this->assertSame("ssh -vvv -F 'php://temp' 'hostc' 'mysqldump -vvv --opt --skip-comments -h'\''database'\'' -u'\''root'\'' -p'\''root'\'' '\''sequelmovie'\'' | (echo '\''CREATE DATABASE IF NOT EXISTS `sequelmovie2`;USE `sequelmovie2`;'\'' && cat)' | mysql -h'127.0.0.1' -P2206 -u'root' -p'root'", $database->generate());
    }

    public function testDatabaseCommandGetter()
    {
        $sshConfig = new SshConfig();
        $sshConfig->setFile(new \SplTempFileObject());
        $database = new DatabaseCommand();
        $database->setName('databaseName')
            ->setSshConfig($sshConfig)
            ->setFromUrl('mysql://root:root@database/sequelmovie')
            ->setFromHost('hostc')
            ->setToUrl('mysql://root:root@127.0.0.1:2206/sequelmovie2')
            ->setToHost('')
            ->setVerbosity(OutputInterface::VERBOSITY_DEBUG)
            ->setExcludes(['exclude1', 'exclude2']);

        $this->assertSame('databaseName', $database->getName());
        $this->assertSame($sshConfig, $database->getSshConfig());
        $this->assertSame(['exclude1', 'exclude2'], $database->getExcludes());
        $this->assertSame('mysql://root:root@database/sequelmovie', $database->getFromUrl());
        $this->assertSame('hostc', $database->getFromHost());
        $this->assertSame('mysql://root:root@127.0.0.1:2206/sequelmovie2', $database->getToUrl());
        $this->assertSame('', $database->getToHost());
        $this->assertSame(OutputInterface::VERBOSITY_DEBUG, $database->getVerbosity());
    }
}
