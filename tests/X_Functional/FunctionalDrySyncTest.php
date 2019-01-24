<?php
declare(strict_types=1);

namespace PHPSu\Tests\X_Functional;

use PHPSu\Config\GlobalConfig;
use PHPSu\Controller;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\BufferedOutput;

final class FunctionalDrySyncTest extends TestCase
{
    public function testFirst(): void
    {
        $config = new GlobalConfig();
        $config->addFilesystem('fileadmin', 'fileadmin');
        $config->addDatabase('database', 'mysql://test:aaaaaaaa@127.0.0.1/testdb');
        $config->addSshConnection('projectEu', 'ssh://project@project.com');
        $config->addAppInstance('testing', 'projectEu', '/srv/www/project/test.project');
        $config->addAppInstance('local', '', './testInstance')
            ->addDatabase('database', 'mysql://root:root@127.0.0.1/test1234');

        $output = new BufferedOutput();
        $controller = new Controller($output, $config);
        $controller->sync('testing', 'local', '', true);
        $lines = [
            '+----------------------+------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+',
            '| Name                 | Bash Command                                                                                                                                                                                               |',
            '+----------------------+------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+',
            "| filesystem:fileadmin | rsync -avz -e 'ssh -F '\''.phpsu/config/ssh_config'\''' 'projectEu:/srv/www/project/test.project/fileadmin/*' './testInstance/fileadmin/'                                                                  |",
            "| database:database    | ssh -F '.phpsu/config/ssh_config' 'projectEu' -C 'mysqldump --opt --skip-comments -h'\''127.0.0.1'\'' -u'\''test'\'' -p'\''aaaaaaaa'\'' '\''testdb'\''' | mysql -h'127.0.0.1' -u'root' -p'root' 'test1234' |",
            '+----------------------+------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+',
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
        $controller = new Controller($output, $config);
        $controller->sync('testing', 'local', '', true);
        $lines = [
            '+----------------------+------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+',
            '| Name                 | Bash Command                                                                                                                                                                                               |',
            '+----------------------+------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+',
            "| filesystem:fileadmin | rsync -avz --exclude='*.mp4' --exclude='*.mp3' --exclude='*.zip' -e 'ssh -F '\''.phpsu/config/ssh_config'\''' 'projectEu:/srv/www/project/test.project/fileadmin/*' './testInstance/fileadmin/'            |",
            "| database:database    | ssh -F '.phpsu/config/ssh_config' 'projectEu' -C 'mysqldump --opt --skip-comments -h'\''127.0.0.1'\'' -u'\''test'\'' -p'\''aaaaaaaa'\'' '\''testdb'\''' | mysql -h'127.0.0.1' -u'root' -p'root' 'test1234' |",
            '+----------------------+------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+',
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
        $controller = new Controller($output, $config);
        $controller->sync('testing', 'local', '', true);
        $lines = [
            '+-------------------+--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+',
            '| Name              | Bash Command                                                                                                                                                                                                                                                                         |',
            '+-------------------+--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+',
            "| database:database | ssh -F '.phpsu/config/ssh_config' 'projectEu' -C 'mysqldump --opt --skip-comments -h'\''127.0.0.1'\'' -u'\''test'\'' -p'\''aaaaaaaa'\'' '\''testdb'\'' --ignore-table='\''testdb.table1'\'' --ignore-table='\''testdb.table2'\''' | mysql -h'127.0.0.1' -u'root' -p'root' 'test1234' |",
            '+-------------------+--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+',
            '',
        ];
        $this->assertSame($lines, explode("\n", $output->fetch()));
    }
}
