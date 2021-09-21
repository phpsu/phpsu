<?php

declare(strict_types=1);

namespace PHPSu\Tests;

use PHPSu\Command\CommandGenerator;
use PHPSu\Config\ConfigurationLoader;
use PHPSu\Config\GlobalConfig;
use PHPSu\Controller;
use PHPSu\Options\MysqlOptions;
use PHPSu\Options\SshOptions;
use PHPSu\Options\SyncOptions;
use PHPSu\Process\CommandExecutor;
use PHPSu\Tests\TestHelper\BufferedConsoleOutput;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\ConsoleOutput;

final class ControllerTest extends TestCase
{
    public function testEmptyConfigSshDryRun(): void
    {
        $output = new BufferedOutput();
        $config = new GlobalConfig();
        $config->addAppInstance('production', 'serverEu', '/var/www/prod');
        $config->addAppInstance('local');
        $controller = new Controller(new CommandGenerator($config));
        $controller->ssh($output, (new SshOptions('production'))->setDryRun(true));
        $this->assertSame("ssh -F '.phpsu/config/ssh_config' 'serverEu' -t 'cd '\''/var/www/prod'\'' ; bash --login'\n", $output->fetch());
    }

    public function testEmptyConfigSyncDryRun(): void
    {
        $output = new BufferedOutput();
        $config = new GlobalConfig();
        $config->addAppInstance('production', 'serverEu', '/var/www/prod');
        $config->addAppInstance('local');
        $controller = new Controller(new CommandGenerator($config));
        $controller->sync($output, (new SyncOptions('production'))->setDryRun(true)->setAll(true));
        $this->assertSame('', $output->fetch());
    }

    public function testEmptyConfigSsh(): void
    {
        $config = new GlobalConfig();
        $config->addAppInstance('production', 'serverEu', '/var/www/prod');
        $config->addAppInstance('local');
        $executor = $this->createMock(CommandExecutor::class);
        $executor->method('passthru')->willReturn(0);
        $controller = new Controller(new CommandGenerator($config), $executor);
        $command = $controller->ssh(new BufferedOutput(), (new SshOptions('production')));
        $this->assertEquals(0, $command);
    }

    public function testMysqlCommandDryRun(): void
    {
        $config = new GlobalConfig();
        $config->addAppInstance('local');
        $config->addDatabase('test', 'web', 'user', '#!;~"', '99.88.77.123');
        $controller = new Controller(new CommandGenerator($config));
        $options = new MysqlOptions();
        $options->setDryRun(true)
            ->setDatabase('test')
            ->setAppInstance('local');
        $output = new BufferedOutput();
        $controller->mysql($output, $options);
        $fetch = trim($output->fetch());
        static::assertEquals('mysql --user=\'user\' --password=\'#!;~"\' --host=99.88.77.123 --port=3306 \'web\'', $fetch);
    }

    public function testMysqlCommand(): void
    {
        $config = new GlobalConfig();
        $config->addSshConnection('test', 'ssh://url@bla.com');
        $instance = $config->addAppInstance('production', 'test');
        $instance->addDatabase('test', 'web', 'user', '#!;~"', '99.88.77.123');
        $instance->addDatabase('test2', 'web', 'user', '#!;~"', '99.88.77.123');
        $executor = $this->createMock(CommandExecutor::class);
        $executor->method('passthru')->willReturn(11);
        $controller = new Controller(new CommandGenerator($config), $executor);
        $options = new MysqlOptions();
        $options
            ->setDatabase('test2')
            ->setCommand('SELECT * FROM web')
            ->setAppInstance('production');
        static::assertEquals(11, $controller->mysql(new BufferedOutput(), $options));
    }

    public function testMysqlCommandInDockerDryRun(): void
    {
        $config = new GlobalConfig();
        $instance = $config->addAppInstance('local', '');
        $instance->addDatabase('test', 'web', 'user', '#!;~"', 'web')->executeInDocker(true);
        $options = new MysqlOptions();
        $options
            ->setDatabase('test')
            ->setAppInstance('local')
            ->setDryRun(true);
        $output = new BufferedOutput();
        (new Controller(new CommandGenerator($config)))->mysql($output, $options);
        $fetch = trim($output->fetch());
        static::assertEquals('docker \'exec\' -it \'web\' mysql --user=\'user\' --password=\'#!;~"\' --host=127.0.0.1 --port=3306 \'web\'', $fetch);
    }

    public function testFilesystemAndDatabase(): void
    {
        $config = new GlobalConfig();
        $config->addFilesystem('fileadmin', 'fileadmin');
        $config->addDatabaseByUrl('database', 'mysql://test:aaaaaaaa@127.0.0.1/testdb');
        $config->addSshConnection('projectEu', 'ssh://project@project.com');
        $config->addAppInstance('testing', 'projectEu', '/srv/www/project/test.project');
        $config->addAppInstance('local', '', './testInstance')
            ->addDatabase('database', 'test1234', 'root', 'root');

        $output = new BufferedOutput();
        $controller = new Controller(new CommandGenerator($config));
        $controller->sync($output, (new SyncOptions('testing'))->setDryRun(true));
        $lines = [
            'filesystem:fileadmin',
            "rsync -az -e 'ssh -F '\''.phpsu/config/ssh_config'\''' 'projectEu:/srv/www/project/test.project/fileadmin/' './testInstance/fileadmin/'",
            'database:database',
            "ssh -F '.phpsu/config/ssh_config' 'projectEu' 'mysqldump --opt --skip-comments --single-transaction --lock-tables=false --host='\''127.0.0.1'\'' --user='\''test'\'' --password='\''aaaaaaaa'\'' '\''testdb'\'' | (echo '\''CREATE DATABASE IF NOT EXISTS `test1234`;USE `test1234`;'\'' && cat)' | mysql --host='127.0.0.1' --user='root' --password='root'",
            '',
        ];
        $this->assertSame($lines, explode("\n", $output->fetch()));
    }

    public function testExcludeShouldBePresentInRsyncCommand(): void
    {
        $config = new GlobalConfig();
        $config->addFilesystem('fileadmin', 'fileadmin')->addExclude('*.mp4')->addExclude('*.mp3')->addExcludes(['*.zip', '*.rar']);
        $config->addDatabaseByUrl('database', 'mysql://test:aaaaaaaa@127.0.0.1/testdb');
        $config->addSshConnection('projectEu', 'ssh://project@project.com');
        $config->addAppInstance('testing', 'projectEu', '/srv/www/project/test.project');
        $config->addAppInstance('local', '', './testInstance')
            ->addDatabaseByUrl('database', 'mysql://root:root@127.0.0.1/test1234');

        $output = new BufferedOutput();
        $controller = new Controller(new CommandGenerator($config));
        $controller->sync($output, (new SyncOptions('testing'))->setDryRun(true));
        $lines = [
            'filesystem:fileadmin',
            "rsync -az --exclude '*.mp4' --exclude '*.mp3' --exclude '*.zip' --exclude '*.rar' -e 'ssh -F '\''.phpsu/config/ssh_config'\''' 'projectEu:/srv/www/project/test.project/fileadmin/' './testInstance/fileadmin/'",
            'database:database',
            "ssh -F '.phpsu/config/ssh_config' 'projectEu' 'mysqldump --opt --skip-comments --single-transaction --lock-tables=false --host='\''127.0.0.1'\'' --user='\''test'\'' --password='\''aaaaaaaa'\'' '\''testdb'\'' | (echo '\''CREATE DATABASE IF NOT EXISTS `test1234`;USE `test1234`;'\'' && cat)' | mysql --host='127.0.0.1' --user='root' --password='root'",
            '',
        ];
        $this->assertSame($lines, explode("\n", $output->fetch()));
    }

    public function testExcludeShouldBePresentInDatabaseCommand(): void
    {
        $config = new GlobalConfig();
        $config->addDatabaseByUrl('database', 'mysql://test:aaaaaaaa@127.0.0.1/testdb')
            ->addExclude('table1')
            ->addExcludes(['table2', 'table4']);
        $config->addSshConnection('projectEu', 'ssh://project@project.com');
        $config->addAppInstance('testing', 'projectEu', '/srv/www/project/test.project');
        $config->addAppInstance('local', '', './testInstance')
            ->addDatabaseByUrl('database', 'mysql://root:root@127.0.0.1/test1234')
            ->addExclude('table1')
            ->addExclude('table3')
            ->addExclude('/cache/')
            ->addExclude('/c/');

        $output = new BufferedOutput();
        $controller = new Controller(new CommandGenerator($config));
        $controller->sync($output, (new SyncOptions('testing'))->setDryRun(true));
        $lines = [
            'database:database',
            "ssh -F '.phpsu/config/ssh_config' 'projectEu' 'TBLIST=`mysql --host='\''127.0.0.1'\'' --user='\''test'\'' --password='\''aaaaaaaa'\'' -AN -e \"SET group_concat_max_len = 51200; SELECT GROUP_CONCAT(table_name separator '\'' '\'') FROM information_schema.tables WHERE table_schema='\''testdb'\'' AND table_name NOT REGEXP '\''cache'\'' AND table_name NOT REGEXP '\''c'\'' AND table_name NOT IN('\''table1'\'','\''table2'\'','\''table4'\'','\''table3'\'')\"` && mysqldump --opt --skip-comments --single-transaction --lock-tables=false --host='\''127.0.0.1'\'' --user='\''test'\'' --password='\''aaaaaaaa'\'' '\''testdb'\'' \${TBLIST} | (echo '\''CREATE DATABASE IF NOT EXISTS `test1234`;USE `test1234`;'\'' && cat)' | mysql --host='127.0.0.1' --user='root' --password='root'",
            '',
        ];
        $this->assertSame($lines, explode("\n", $output->fetch()));
    }

    public function testExcludeShouldBePresentInDatabaseCommandWithDockerEnabled(): void
    {
        $config = new GlobalConfig();
        $config->addDatabaseByUrl('database', 'mysql://test:aaaaaaaa@127.0.0.1/testdb')
            ->addExclude('table1')
            ->setContainer('test')
            ->executeInDocker(true);
        $config->addSshConnection('projectEu', 'ssh://project@project.com');
        $config->addAppInstance('testing', 'projectEu', '/srv/www/project/test.project');
        $config->addAppInstance('local', '', './testInstance')
            ->addDatabaseByUrl('database', 'mysql://root:root@127.0.0.1/test1234')
            ->addExclude('table1');

        $output = new BufferedOutput();
        $controller = new Controller(new CommandGenerator($config));
        $controller->sync($output, (new SyncOptions('testing'))->setDryRun(true));
        $lines = "ssh -F '.phpsu/config/ssh_config' 'projectEu' 'TBLIST=`docker '\''exec'\'' -i '\''test'\'' mysql --host='\''127.0.0.1'\'' --user='\''test'\'' --password='\''aaaaaaaa'\'' -AN -e \"SET group_concat_max_len = 51200; SELECT GROUP_CONCAT(table_name separator '\'' '\'') FROM information_schema.tables WHERE table_schema='\''testdb'\'' AND table_name NOT IN('\''table1'\'')\"` && docker '\''exec'\'' -i -e '\''TBLIST='\''\'\'''\''\${TBLIST}'\''\'\'''\'''\'' '\''test'\'' mysqldump --opt --skip-comments --single-transaction --lock-tables=false --host='\''127.0.0.1'\'' --user='\''test'\'' --password='\''aaaaaaaa'\'' '\''testdb'\'' \${TBLIST} | (echo '\''CREATE DATABASE IF NOT EXISTS `test1234`;USE `test1234`;'\'' && cat)' | mysql --host='127.0.0.1' --user='root' --password='root'";
        static::assertSame($lines, trim(explode("\n", $output->fetch())[1]));
    }

    public function testAllOptionShouldOverwriteExcludes(): void
    {
        $config = new GlobalConfig();
        $config->addFilesystem('fileadmin', 'fileadmin')->addExclude('*.mp4')->addExclude('*.mp3')->addExcludes(['*.zip', '*.rar']);
        $config->addDatabaseByUrl('database', 'mysql://test:aaaaaaaa@127.0.0.1/testdb')->addExclude('table1')->addExclude('table2')->addExcludes(['table3', 'table4']);
        $config->addSshConnection('projectEu', 'ssh://project@project.com');
        $config->addAppInstance('testing', 'projectEu', '/srv/www/project/test.project');
        $config->addAppInstance('local', '', './testInstance')
            ->addDatabaseByUrl('database', 'mysql://root:root@127.0.0.1/test1234')->addExclude('table1')->addExclude('table1');

        $output = new BufferedOutput();
        $controller = new Controller(new CommandGenerator($config));
        $controller->sync($output, (new SyncOptions('testing'))->setDryRun(true)->setAll(true));
        $lines = [
            'filesystem:fileadmin',
            "rsync -az -e 'ssh -F '\''.phpsu/config/ssh_config'\''' 'projectEu:/srv/www/project/test.project/fileadmin/' './testInstance/fileadmin/'",
            'database:database',
            "ssh -F '.phpsu/config/ssh_config' 'projectEu' 'mysqldump --opt --skip-comments --single-transaction --lock-tables=false --host='\''127.0.0.1'\'' --user='\''test'\'' --password='\''aaaaaaaa'\'' '\''testdb'\'' | (echo '\''CREATE DATABASE IF NOT EXISTS `test1234`;USE `test1234`;'\'' && cat)' | mysql --host='127.0.0.1' --user='root' --password='root'",
            '',
        ];
        $this->assertSame($lines, explode("\n", $output->fetch()));
    }

    public function testNoDbOptionShouldRemoveDatabaseCommand(): void
    {
        $config = new GlobalConfig();
        $config->addFilesystem('fileadmin', 'fileadmin')->addExclude('*.mp4')->addExclude('*.mp3')->addExcludes(['*.zip', '*.rar']);
        $config->addDatabaseByUrl('database', 'mysql://test:aaaaaaaa@127.0.0.1/testdb')->addExclude('table1')->addExclude('table2')->addExcludes(['table3', 'table4']);
        $config->addSshConnection('projectEu', 'ssh://project@project.com');
        $config->addAppInstance('testing', 'projectEu', '/srv/www/project/test.project');
        $config->addAppInstance('local', '', './testInstance')
            ->addDatabaseByUrl('database', 'mysql://root:root@127.0.0.1/test1234')->addExclude('table1')->addExclude('table1');

        $output = new BufferedOutput();
        $controller = new Controller(new CommandGenerator($config));
        $controller->sync($output, (new SyncOptions('testing'))->setDryRun(true)->setAll(true)->setNoDatabases(true));
        $lines = [
            'filesystem:fileadmin',
            "rsync -az -e 'ssh -F '\''.phpsu/config/ssh_config'\''' 'projectEu:/srv/www/project/test.project/fileadmin/' './testInstance/fileadmin/'",
            '',
        ];
        $this->assertSame($lines, explode("\n", $output->fetch()));
    }

    public function testNoFileOptionShouldRemoveDatabaseCommand(): void
    {
        $config = new GlobalConfig();
        $config->addFilesystem('fileadmin', 'fileadmin')->addExclude('*.mp4')->addExclude('*.mp3')->addExcludes(['*.zip', '*.rar']);
        $config->addDatabaseByUrl('database', 'mysql://test:aaaaaaaa@127.0.0.1/testdb')->addExclude('table1')->addExclude('table2')->addExcludes(['table3', 'table4']);
        $config->addSshConnection('projectEu', 'ssh://project@project.com');
        $config->addAppInstance('testing', 'projectEu', '/srv/www/project/test.project');
        $config->addAppInstance('local', '', './testInstance')
            ->addDatabaseByUrl('database', 'mysql://root:root@127.0.0.1/test1234')->addExclude('table1')->addExclude('table1');

        $output = new BufferedOutput();
        $controller = new Controller(new CommandGenerator($config));
        $controller->sync($output, (new SyncOptions('testing'))->setDryRun(true)->setAll(true)->setNoFiles(true));
        $lines = [
            'database:database',
            "ssh -F '.phpsu/config/ssh_config' 'projectEu' 'mysqldump --opt --skip-comments --single-transaction --lock-tables=false --host='\''127.0.0.1'\'' --user='\''test'\'' --password='\''aaaaaaaa'\'' '\''testdb'\'' | (echo '\''CREATE DATABASE IF NOT EXISTS `test1234`;USE `test1234`;'\'' && cat)' | mysql --host='127.0.0.1' --user='root' --password='root'",
            '',
        ];
        $this->assertSame($lines, explode("\n", $output->fetch()));
    }

    public function testUseCaseWithoutGlobalDatabase(): void
    {
        $config = new GlobalConfig();
        $config->addSshConnection('projectEu', 'ssh://project@project.com');
        $config->addAppInstance('testing', 'projectEu', '/srv/www/project/test.project')
            ->addDatabaseByUrl('database', 'mysql://test:aaaaaaaa@127.0.0.1/testdb');
        $config->addAppInstance('local', '', './testInstance')
            ->addDatabaseByUrl('database', 'mysql://root:root@127.0.0.1/test1234')->addExclude('table1')->addExclude('table1');

        $output = new BufferedOutput();
        $controller = new Controller(new CommandGenerator($config));
        $controller->sync($output, (new SyncOptions('testing'))->setDryRun(true)->setAll(true)->setNoFiles(true));
        $lines = [
            'database:database',
            "ssh -F '.phpsu/config/ssh_config' 'projectEu' 'mysqldump --opt --skip-comments --single-transaction --lock-tables=false --host='\''127.0.0.1'\'' --user='\''test'\'' --password='\''aaaaaaaa'\'' '\''testdb'\'' | (echo '\''CREATE DATABASE IF NOT EXISTS `test1234`;USE `test1234`;'\'' && cat)' | mysql --host='127.0.0.1' --user='root' --password='root'",
            '',
        ];
        $this->assertSame($lines, explode(PHP_EOL, $output->fetch()));
    }

    public function testUseCaseDatabaseOnlyDefinedOnOneEnd(): void
    {
        $config = new GlobalConfig();
        $config->addSshConnection('projectEu', 'ssh://project@project.com');
        $config->addDatabaseByUrl('database', 'mysql://root:root@127.0.0.1/test1234');
        $testingApp = $config->addAppInstance('testing', 'projectEu', '/srv/www/project/test.project');
        $testingApp->addDatabaseByUrl('database', 'mysql://test:aaaaaaaa@127.0.0.1/testdb');
        $testingApp->addDatabaseByUrl('database2', 'mysql://test:aaaaaaaa@127.0.0.1/testdb2');
        $config->addAppInstance('local', '', './testInstance')
            ->addDatabaseByUrl('database2', 'mysql://root:root@127.0.0.1/test1234_2');

        $output = new BufferedOutput();
        $controller = new Controller(new CommandGenerator($config));
        $controller->sync($output, (new SyncOptions('testing'))->setDryRun(true)->setAll(true)->setNoFiles(true));
        $lines = [
            'database:database',
            "ssh -F '.phpsu/config/ssh_config' 'projectEu' 'mysqldump --opt --skip-comments --single-transaction --lock-tables=false --host='\''127.0.0.1'\'' --user='\''test'\'' --password='\''aaaaaaaa'\'' '\''testdb'\'' | (echo '\''CREATE DATABASE IF NOT EXISTS `test1234`;USE `test1234`;'\'' && cat)' | mysql --host='127.0.0.1' --user='root' --password='root'",
            'database:database2',
            "ssh -F '.phpsu/config/ssh_config' 'projectEu' 'mysqldump --opt --skip-comments --single-transaction --lock-tables=false --host='\''127.0.0.1'\'' --user='\''test'\'' --password='\''aaaaaaaa'\'' '\''testdb2'\'' | (echo '\''CREATE DATABASE IF NOT EXISTS `test1234_2`;USE `test1234_2`;'\'' && cat)' | mysql --host='127.0.0.1' --user='root' --password='root'",
            '',
        ];
        $this->assertSame($lines, explode("\n", $output->fetch()));
    }

    public function testPhpApiReadmeExample(): void
    {
        $oldCwd = getcwd();
        assert(is_string($oldCwd));
        chdir(__DIR__ . '/fixtures');
        $config = (new ConfigurationLoader())->getConfig();
        chdir($oldCwd);

        $log = new BufferedOutput();
        $syncOptions = new SyncOptions('production');
        $syncOptions->setDryRun(true);
        $phpsu = new Controller(new CommandGenerator($config));
        $phpsu->sync($log, $syncOptions);

        $this->assertSame('filesystem:var/storage' . PHP_EOL . 'rsync -az \'testProduction/var/storage/\' \'testLocal/var/storage/\'' . PHP_EOL, $log->fetch());
    }

    public function testSyncOutputHasSectionsWithEmptyConfigAndConsoleOutput(): void
    {
        $config = new GlobalConfig();
        $config->addAppInstance('production', 'localhost', __DIR__);
        $config->addAppInstance('local');
        $controller = new Controller(new CommandGenerator($config));
        $syncOptions = new SyncOptions('production');
        $syncOptions->setNoDatabases(true);
        $syncOptions->setNoFiles(true);
        $output = new BufferedConsoleOutput();
        $controller->sync($output, $syncOptions);
        rewind($output->getStream());
        $this->assertSame("--------------------\n", stream_get_contents($output->getStream()), 'Asserting result empty since config is empty as well');
    }

    public function testSyncOutputHasSectionsWithEmptyConfigAndBufferedOutput(): void
    {
        $config = new GlobalConfig();
        $config->addAppInstance('production', 'localhost', __DIR__);
        $config->addAppInstance('local');
        $controller = new Controller(new CommandGenerator($config));
        $syncOptions = new SyncOptions('local');
        $syncOptions->setSource('local');
        $syncOptions->setNoDatabases(true);
        $syncOptions->setNoFiles(true);
        $syncOptions->setDestination('production');
        $output = new BufferedOutput();
        $controller->sync($output, $syncOptions);
        $this->assertSame('', $output->fetch(), 'Excepting sync to do nothing');
    }

    public function testSshOutputPassthruExecution(): void
    {
        $config = new GlobalConfig();
        $controller = new Controller(new CommandGenerator($config));
        $config->addAppInstance('production', '127.0.0.1', __DIR__);
        $config->addAppInstance('local');
        $sshOptions = (new SshOptions('typo'))->setDestination('local');
        $output = new BufferedOutput();
        $this->expectExceptionMessage('the found host and the current Host are the same');
        $controller->ssh($output, $sshOptions);
    }
}
