<?php
declare(strict_types=1);

namespace PHPSu\Tests\Alpha;

use PHPSu\Alpha\DatabaseCommand;
use PHPSu\Alpha\RsyncCommand;
use PHPSu\Alpha\SshCommand;
use PHPSu\Alpha\SshConfig;
use PHPSu\Alpha\SshConfigHost;
use PHPUnit\Framework\TestCase;

final class RunTest extends TestCase
{
    public function testSshConfig()
    {
        $sshConfig = new SshConfig();
        $sshConfig->hostc = new SshConfigHost();
        $sshConfig->hostc->User = 'user';
        $sshConfig->hostc->HostName = 'host_c';
        $sshConfig->hostc->ProxyJump = 'hostb';

        $sshConfig->hostb = new SshConfigHost();
        $sshConfig->hostb->User = 'user';
        $sshConfig->hostb->HostName = 'host_b';
        $sshConfig->hostb->ProxyJump = 'hosta';

        $sshConfig->hosta = new SshConfigHost();
        $sshConfig->hosta->User = 'user';
        $sshConfig->hosta->HostName = 'localhost';
        $sshConfig->hosta->Port = '2208';

        $sshConfig->{'*'} = new SshConfigHost();
        $sshConfig->{'*'}->StrictHostKeyChecking = 'no';
        $sshConfig->{'*'}->UserKnownHostsFile = '/dev/null';
        $sshConfig->{'*'}->IdentityFile = './docker/testCaseD/id_rsa';

        $sshConfig->writeConfig($file = new \SplTempFileObject());
        $this->assertSame(implode('', iterator_to_array($file)), <<<'SSH_CONFIG'
Host hostc
  HostName host_c
  ProxyJump hostb
  User user

Host hostb
  HostName host_b
  ProxyJump hosta
  User user

Host hosta
  HostName localhost
  Port 2208
  User user

Host *
  IdentityFile ./docker/testCaseD/id_rsa
  StrictHostKeyChecking no
  UserKnownHostsFile /dev/null


SSH_CONFIG
        );
    }

    public function testSshCommand()
    {
        $sshConfig = new SshConfig();
        $ssh = new SshCommand();
        $ssh->setSshConfig($sshConfig)
            ->setInto('hosta');
        $this->assertSame('ssh -F .phpsu/config/ssh_config hosta', $ssh->generate());
    }

    public function testRsyncCommand()
    {
        $sshConfig = new SshConfig();
        $rsync = new RsyncCommand();
        $rsync->setSshConfig($sshConfig)
            ->setOptions('-avz')
            ->setFrom('hosta:~/test/*')
            ->setTo('./__test/');

        $this->assertSame('rsync -avz -e "ssh -F .phpsu/config/ssh_config" hosta:~/test/* ./__test/', $rsync->generate());
    }

    public function testDatabaseCommand()
    {
        $sshConfig = new SshConfig();
        $database = new DatabaseCommand();
        $database->setSshConfig($sshConfig)
            ->setFromUrl('mysql://root:root@database/sequelmovie')
            ->setFromHost('hostc')
            ->setToUrl('mysql://root:root@127.0.0.1:2206/sequelmovie2')
            ->setToHost('');

        $this->assertSame('ssh -F .phpsu/config/ssh_config hostc -C "mysqldump -hdatabase -P3306 -uroot -proot sequelmovie" | mysql -h127.0.0.1 -P2206 -uroot -proot sequelmovie2', $database->generate());
    }
}
