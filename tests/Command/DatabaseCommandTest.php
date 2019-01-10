<?php
declare(strict_types=1);

namespace PHPSu\Tests\Command;

use PHPSu\Command\DatabaseCommand;
use PHPSu\Config\SshConfig;
use PHPUnit\Framework\TestCase;

final class DatabaseCommandTest extends TestCase
{

    public function testGenerate()
    {
        $sshConfig = new SshConfig();
        $sshConfig->setFile(new \SplTempFileObject());
        $database = new DatabaseCommand();
        $database->setSshConfig($sshConfig)
            ->setFromUrl('mysql://root:root@database/sequelmovie')
            ->setFromHost('hostc')
            ->setToUrl('mysql://root:root@127.0.0.1:2206/sequelmovie2')
            ->setToHost('');

        $this->assertSame('ssh -F php://temp hostc -C "mysqldump --skip-comments --extended-insert -hdatabase -P3306 -uroot -proot sequelmovie" | mysql -h127.0.0.1 -P2206 -uroot -proot sequelmovie2', $database->generate());
    }
}
