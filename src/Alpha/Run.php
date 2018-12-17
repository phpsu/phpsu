<?php
declare(strict_types=1);

namespace PHPSu\Alpha;

final class Run
{
    public function __construct()
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

        $ssh = new SshCommand();
        $ssh->setSshConfig($sshConfig)
            ->setInto('hosta');

        $rsync = new RsyncCommand();
        $rsync->setSshConfig($sshConfig)
            ->setOptions('-avz')
            ->setFrom('hosta:~/test/*')
            ->setTo('./__test/');

        $database = new DatabaseCommand();
        $database->setSshConfig($sshConfig)
            ->setFromUrl('mysql://root:root@database/sequelmovie')
            ->setFromHost('hostc')
            ->setToUrl('mysql://root:root@127.0.0.1:2206/sequelmovie2')
            ->setToHost('');

        if ($ssh->generate() !== 'ssh -F ./.phpsu/config/ssh_config hosta') {
            throw new \Exception('ERROR');
        }
        if ($rsync->generate() !== 'rsync -avz -e "ssh -F ./.phpsu/config/ssh_config" hosta:~/test/* ./__test/') {
            throw new \Exception('ERROR');
        }
        if ($database->generate() !== 'ssh -F ./.phpsu/config/ssh_config hostc -C "mysqldump -hdatabase -P3306 -uroot -proot sequelmovie" | mysql -h127.0.0.1 -P2206 -uroot -proot sequelmovie2') {
            throw new \Exception('ERROR');
        }
    }
}
