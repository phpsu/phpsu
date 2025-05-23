<?php

declare(strict_types=1);

namespace PHPSu\Tests\Command;

use Exception;
use PHPSu\Command\MysqlCommand;
use PHPSu\Config\GlobalConfig;
use PHPSu\Config\SshConfig;
use PHPSu\ShellCommandBuilder\ShellBuilder;
use PHPUnit\Framework\TestCase;
use SplTempFileObject;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class MysqlCommandTest
 * @package PHPSu\Tests\Command
 */
final class MysqlCommandTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testDatabaseCommandGenerate(): void
    {
        $sshConfig = new SshConfig();
        $sshConfig->setFile(new SplTempFileObject());

        $global = new GlobalConfig();
        $global->addAppInstance('local')
            ->addDatabase('app', 'test', 'a', 'b');
        $database = MysqlCommand::fromGlobal(
            $global,
            'local',
            'app'
        );
        $database->setSshConfig($sshConfig);
        self::assertSame("mysql --user='a' --password='b' --host=127.0.0.1 --port=3306 'test'", (string)$database->generate(ShellBuilder::new()));
    }

    /**
     * @throws Exception
     */
    public function testDatabaseCommandGenerateWithGlobalDatabase(): void
    {
        $sshConfig = new SshConfig();
        $sshConfig->setFile(new SplTempFileObject());

        $global = new GlobalConfig();
        $global->addAppInstance('local');
        $global->addDatabase('app', 'test', 'a', 'b');

        $database = MysqlCommand::fromGlobal($global, 'local');
        $database->setSshConfig($sshConfig);
        $database->setVerbosity(OutputInterface::VERBOSITY_DEBUG);
        self::assertSame("mysql -vvv --user='a' --password='b' --host=127.0.0.1 --port=3306 'test'", (string)$database->generate(ShellBuilder::new()));
    }

    public function testDatabaseCommandGenerateWithTwoDatabases(): void
    {
        $sshConfig = new SshConfig();
        $sshConfig->setFile(new SplTempFileObject());

        $global = new GlobalConfig();
        $instance = $global->addAppInstance('local');
        $instance->addDatabase('app', 'test', 'a', 'b');
        $instance->addDatabase('app2', 'test', 'a', 'b');
        self::expectException(Exception::class);
        self::expectExceptionMessage('There are multiple databases defined, please specify the one to connect to.');
        $database = MysqlCommand::fromGlobal(
            $global,
            'local'
        );
        $database->setSshConfig($sshConfig);
    }

    public function testDatabaseCommandGenerateWithTwoDatabasesOnGlobal(): void
    {
        $sshConfig = new SshConfig();
        $sshConfig->setFile(new SplTempFileObject());

        $global = new GlobalConfig();
        $global->addAppInstance('local');
        $global->addDatabase('app', 'test', 'a', 'b');
        $global->addDatabase('app2', 'test', 'a', 'b');
        self::expectException(Exception::class);
        self::expectExceptionMessage('There are multiple databases defined, please specify the one to connect to.');
        $database = MysqlCommand::fromGlobal(
            $global,
            'local'
        );
        $database->setSshConfig($sshConfig);
    }

    /**
     * @throws Exception
     */
    public function testDatabaseCommandGenerateWithMysqlCommand(): void
    {
        $sshConfig = new SshConfig();
        $sshConfig->setFile(new SplTempFileObject());

        $global = new GlobalConfig();
        $global->addAppInstance('local')
            ->addDatabase('app', 'test', 'a', 'b');
        $database = MysqlCommand::fromGlobal(
            $global,
            'local',
            'app'
        );
        $database->setSshConfig($sshConfig);
        $database->setCommand('SELECT * FROM tables');

        $result = $database->generate()->jsonSerialize();
        self::assertCount(1, $result);
        $mysql = $result[0] ?? [];
        self::assertIsArray($mysql);
        self::assertSame('mysql', $mysql['executable'] ?? null);
        self::assertIsArray($mysql['arguments']);
        self::assertCount(6, $mysql['arguments']);
        $sqlCommand = $mysql['arguments'][5] ?? null;
        self::assertIsArray($sqlCommand);
        self::assertTrue($sqlCommand['isShortOption']);
        self::assertEquals("'SELECT * FROM tables'", $sqlCommand['value']);
        self::assertEquals("e", $sqlCommand['argument']);
    }

    /**
     * @throws Exception
     */
    public function testRemoteDatabaseCommandGenerate(): void
    {
        $sshConfig = new SshConfig();
        $sshConfig->setFile(new SplTempFileObject());

        $global = new GlobalConfig();
        $global->addSshConnection('prod', 'ssh://root@example.com');
        $global->addAppInstance('production', 'prod')
            ->addDatabase('app', 'test', 'a', 'b');
        $database = MysqlCommand::fromGlobal(
            $global,
            'production',
            'app'
        );
        $database->setSshConfig($sshConfig);

        $result = $database->generate()->jsonSerialize();
        self::assertCount(1, $result);
        $ssh = $result[0];
        self::assertIsArray($ssh);
        self::assertSame('ssh', $ssh['executable']);
        self::assertIsArray($ssh['arguments']);
        self::assertCount(4, $ssh['arguments']);
        $mysql = ShellBuilder::command('mysql')
            ->addOption('user', 'a', true, true)
            ->addOption('password', 'b', true, true)
            ->addOption('host', '127.0.0.1', false, true)
            ->addOption('port', '3306', false, true)
            ->addArgument('test')
        ;
        $arg = $ssh['arguments'][0] ?? null;
        self::assertIsArray($arg);
        self::assertEquals('t', $arg['argument'] ?? null);
        $arg = $ssh['arguments'][3] ?? null;
        self::assertIsArray($arg);
        self::assertEquals($mysql->__toArray(), $arg['value'] ?? null);
    }
}
