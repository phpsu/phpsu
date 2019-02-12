<?php
declare(strict_types=1);

namespace PHPSu\Tests;

use PHPSu\Config\GlobalConfig;
use PHPSu\Controller;
use PHPSu\SshOptions;
use PHPSu\SyncOptions;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\BufferedOutput;

final class ControllerTest extends TestCase
{
    public function testEmptyConfigSshDryRun(): void
    {
        $output = new BufferedOutput();
        $config = new GlobalConfig();
        $config->addAppInstance('production', 'serverEu', '/var/www/prod');
        $config->addAppInstance('local');
        $controller = new Controller();
        $controller->ssh($output, $config, (new SshOptions('production'))->setDryRun(true));
        $this->assertSame("ssh -F '.phpsu/config/ssh_config' 'serverEu' -t 'cd '\''/var/www/prod'\''; bash --login'\n", $output->fetch());
    }

    public function testEmptyConfigSyncDryRun(): void
    {
        $output = new BufferedOutput();
        $config = new GlobalConfig();
        $config->addAppInstance('production', 'serverEu', '/var/www/prod');
        $config->addAppInstance('local');
        $controller = new Controller();
        $controller->sync($output, $config, (new SyncOptions('production'))->setDryRun(true)->setAll(true));
        $this->assertSame('', $output->fetch());
    }

    public function testFilesystemAndDatabase(): void
    {
        $config = new GlobalConfig();
        $config->addFilesystem('fileadmin', 'fileadmin');
        $config->addDatabase('database', 'mysql://test:aaaaaaaa@127.0.0.1/testdb');
        $config->addSshConnection('projectEu', 'ssh://project@project.com');
        $config->addAppInstance('testing', 'projectEu', '/srv/www/project/test.project');
        $config->addAppInstance('local', '', './testInstance')
            ->addDatabase('database', 'mysql://root:root@127.0.0.1/test1234');

        $output = new BufferedOutput();
        $controller = new Controller();
        $controller->sync($output, $config, (new SyncOptions('testing'))->setDryRun(true));
        $lines = [
            'filesystem:fileadmin',
            "rsync -az -e 'ssh -F '\''.phpsu/config/ssh_config'\''' 'projectEu:/srv/www/project/test.project/fileadmin/*' './testInstance/fileadmin/'",
            'database:database',
            "ssh -F '.phpsu/config/ssh_config' 'projectEu' 'mysqldump --opt --skip-comments -h'\''127.0.0.1'\'' -u'\''test'\'' -p'\''aaaaaaaa'\'' '\''testdb'\''' | mysql -h'127.0.0.1' -u'root' -p'root' 'test1234'",
            '',
        ];
        $this->assertSame($lines, explode("\n", $output->fetch()));
    }

    public function testExcludeShouldBePresentInRsyncCommand(): void
    {
        $config = new GlobalConfig();
        $config->addFilesystem('fileadmin', 'fileadmin')->addExclude('*.mp4')->addExclude('*.mp3')->addExclude('*.zip');
        $config->addDatabase('database', 'mysql://test:aaaaaaaa@127.0.0.1/testdb');
        $config->addSshConnection('projectEu', 'ssh://project@project.com');
        $config->addAppInstance('testing', 'projectEu', '/srv/www/project/test.project');
        $config->addAppInstance('local', '', './testInstance')
            ->addDatabase('database', 'mysql://root:root@127.0.0.1/test1234');

        $output = new BufferedOutput();
        $controller = new Controller();
        $controller->sync($output, $config, (new SyncOptions('testing'))->setDryRun(true));
        $lines = [
            'filesystem:fileadmin',
            "rsync -az --exclude='*.mp4' --exclude='*.mp3' --exclude='*.zip' -e 'ssh -F '\''.phpsu/config/ssh_config'\''' 'projectEu:/srv/www/project/test.project/fileadmin/*' './testInstance/fileadmin/'",
            'database:database',
            "ssh -F '.phpsu/config/ssh_config' 'projectEu' 'mysqldump --opt --skip-comments -h'\''127.0.0.1'\'' -u'\''test'\'' -p'\''aaaaaaaa'\'' '\''testdb'\''' | mysql -h'127.0.0.1' -u'root' -p'root' 'test1234'",
            '',
        ];
        $this->assertSame($lines, explode("\n", $output->fetch()));
    }

    public function testExcludeShouldBePresentInDatabaseCommand(): void
    {
        $config = new GlobalConfig();
        $config->addDatabase('database', 'mysql://test:aaaaaaaa@127.0.0.1/testdb')->addExclude('table1')->addExclude('table2');
        $config->addSshConnection('projectEu', 'ssh://project@project.com');
        $config->addAppInstance('testing', 'projectEu', '/srv/www/project/test.project');
        $config->addAppInstance('local', '', './testInstance')
            ->addDatabase('database', 'mysql://root:root@127.0.0.1/test1234')->addExclude('table1')->addExclude('table1');

        $output = new BufferedOutput();
        $controller = new Controller();
        $controller->sync($output, $config, (new SyncOptions('testing'))->setDryRun(true));
        $lines = [
            'database:database',
            "ssh -F '.phpsu/config/ssh_config' 'projectEu' 'mysqldump --opt --skip-comments -h'\''127.0.0.1'\'' -u'\''test'\'' -p'\''aaaaaaaa'\'' '\''testdb'\'' --ignore-table='\''testdb.table1'\'' --ignore-table='\''testdb.table2'\''' | mysql -h'127.0.0.1' -u'root' -p'root' 'test1234'",
            '',
        ];
        $this->assertSame($lines, explode("\n", $output->fetch()));
    }

    public function testAllOptionShouldOverwriteExcludes(): void
    {
        $config = new GlobalConfig();
        $config->addFilesystem('fileadmin', 'fileadmin')->addExclude('*.mp4')->addExclude('*.mp3')->addExclude('*.zip');
        $config->addDatabase('database', 'mysql://test:aaaaaaaa@127.0.0.1/testdb')->addExclude('table1')->addExclude('table2');
        $config->addSshConnection('projectEu', 'ssh://project@project.com');
        $config->addAppInstance('testing', 'projectEu', '/srv/www/project/test.project');
        $config->addAppInstance('local', '', './testInstance')
            ->addDatabase('database', 'mysql://root:root@127.0.0.1/test1234')->addExclude('table1')->addExclude('table1');

        $output = new BufferedOutput();
        $controller = new Controller();
        $controller->sync($output, $config, (new SyncOptions('testing'))->setDryRun(true)->setAll(true));
        $lines = [
            'filesystem:fileadmin',
            "rsync -az -e 'ssh -F '\''.phpsu/config/ssh_config'\''' 'projectEu:/srv/www/project/test.project/fileadmin/*' './testInstance/fileadmin/'",
            'database:database',
            "ssh -F '.phpsu/config/ssh_config' 'projectEu' 'mysqldump --opt --skip-comments -h'\''127.0.0.1'\'' -u'\''test'\'' -p'\''aaaaaaaa'\'' '\''testdb'\''' | mysql -h'127.0.0.1' -u'root' -p'root' 'test1234'",
            '',
        ];
        $this->assertSame($lines, explode("\n", $output->fetch()));
    }

    public function testNoDbOptionShouldRemoveDatabaseCommand(): void
    {
        $config = new GlobalConfig();
        $config->addFilesystem('fileadmin', 'fileadmin')->addExclude('*.mp4')->addExclude('*.mp3')->addExclude('*.zip');
        $config->addDatabase('database', 'mysql://test:aaaaaaaa@127.0.0.1/testdb')->addExclude('table1')->addExclude('table2');
        $config->addSshConnection('projectEu', 'ssh://project@project.com');
        $config->addAppInstance('testing', 'projectEu', '/srv/www/project/test.project');
        $config->addAppInstance('local', '', './testInstance')
            ->addDatabase('database', 'mysql://root:root@127.0.0.1/test1234')->addExclude('table1')->addExclude('table1');

        $output = new BufferedOutput();
        $controller = new Controller();
        $controller->sync($output, $config, (new SyncOptions('testing'))->setDryRun(true)->setAll(true)->setNoDatabases(true));
        $lines = [
            'filesystem:fileadmin',
            "rsync -az -e 'ssh -F '\''.phpsu/config/ssh_config'\''' 'projectEu:/srv/www/project/test.project/fileadmin/*' './testInstance/fileadmin/'",
            '',
        ];
        $this->assertSame($lines, explode("\n", $output->fetch()));
    }

    public function testNoFileOptionShouldRemoveDatabaseCommand(): void
    {
        $config = new GlobalConfig();
        $config->addFilesystem('fileadmin', 'fileadmin')->addExclude('*.mp4')->addExclude('*.mp3')->addExclude('*.zip');
        $config->addDatabase('database', 'mysql://test:aaaaaaaa@127.0.0.1/testdb')->addExclude('table1')->addExclude('table2');
        $config->addSshConnection('projectEu', 'ssh://project@project.com');
        $config->addAppInstance('testing', 'projectEu', '/srv/www/project/test.project');
        $config->addAppInstance('local', '', './testInstance')
            ->addDatabase('database', 'mysql://root:root@127.0.0.1/test1234')->addExclude('table1')->addExclude('table1');

        $output = new BufferedOutput();
        $controller = new Controller();
        $controller->sync($output, $config, (new SyncOptions('testing'))->setDryRun(true)->setAll(true)->setNoFiles(true));
        $lines = [
            'database:database',
            "ssh -F '.phpsu/config/ssh_config' 'projectEu' 'mysqldump --opt --skip-comments -h'\''127.0.0.1'\'' -u'\''test'\'' -p'\''aaaaaaaa'\'' '\''testdb'\''' | mysql -h'127.0.0.1' -u'root' -p'root' 'test1234'",
            '',
        ];
        $this->assertSame($lines, explode("\n", $output->fetch()));
    }

    public function testUseCaseWithoutGlobalDatabase(): void
    {
        $config = new GlobalConfig();
        $config->addSshConnection('projectEu', 'ssh://project@project.com');
        $config->addAppInstance('testing', 'projectEu', '/srv/www/project/test.project')
            ->addDatabase('database', 'mysql://test:aaaaaaaa@127.0.0.1/testdb');
        $config->addAppInstance('local', '', './testInstance')
            ->addDatabase('database', 'mysql://root:root@127.0.0.1/test1234')->addExclude('table1')->addExclude('table1');

        $output = new BufferedOutput();
        $controller = new Controller();
        $controller->sync($output, $config, (new SyncOptions('testing'))->setDryRun(true)->setAll(true)->setNoFiles(true));
        $lines = [
            'database:database',
            "ssh -F '.phpsu/config/ssh_config' 'projectEu' 'mysqldump --opt --skip-comments -h'\''127.0.0.1'\'' -u'\''test'\'' -p'\''aaaaaaaa'\'' '\''testdb'\''' | mysql -h'127.0.0.1' -u'root' -p'root' 'test1234'",
            '',
        ];
        $this->assertSame($lines, explode(PHP_EOL, $output->fetch()));
    }

    public function testUseCaseDatabaseOnlyDefinedOnOneEnd(): void
    {
        $config = new GlobalConfig();
        $config->addSshConnection('projectEu', 'ssh://project@project.com');
        $config->addDatabase('database', 'mysql://root:root@127.0.0.1/test1234');
        $testingApp = $config->addAppInstance('testing', 'projectEu', '/srv/www/project/test.project');
        $testingApp->addDatabase('database', 'mysql://test:aaaaaaaa@127.0.0.1/testdb');
        $testingApp->addDatabase('database2', 'mysql://test:aaaaaaaa@127.0.0.1/testdb2');
        $config->addAppInstance('local', '', './testInstance')
            ->addDatabase('database2', 'mysql://root:root@127.0.0.1/test1234_2');

        $output = new BufferedOutput();
        $controller = new Controller();
        $controller->sync($output, $config, (new SyncOptions('testing'))->setDryRun(true)->setAll(true)->setNoFiles(true));
        $lines = [
            'database:database',
            "ssh -F '.phpsu/config/ssh_config' 'projectEu' 'mysqldump --opt --skip-comments -h'\''127.0.0.1'\'' -u'\''test'\'' -p'\''aaaaaaaa'\'' '\''testdb'\''' | mysql -h'127.0.0.1' -u'root' -p'root' 'test1234'",
            'database:database2',
            "ssh -F '.phpsu/config/ssh_config' 'projectEu' 'mysqldump --opt --skip-comments -h'\''127.0.0.1'\'' -u'\''test'\'' -p'\''aaaaaaaa'\'' '\''testdb2'\''' | mysql -h'127.0.0.1' -u'root' -p'root' 'test1234_2'",
            '',
        ];
        $this->assertSame($lines, explode("\n", $output->fetch()));
    }
}
