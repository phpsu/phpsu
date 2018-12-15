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

        $ssh = new SshCmd();
        $ssh->setSshConfig($sshConfig)
            ->setInto('hosta');

        $rsync = new RsyncCmd();
        $rsync->setSshConfig($sshConfig)
            ->setOptions('-avz')
            ->setFrom('hosta:~/test/*')
            ->setTo('./__test/');

        $database = new DatabaseCmd();
        $database->setSshConfig($sshConfig)
            ->setFromUrl('mysql://root:root@database/sequelmovie')
            ->setFromHost('hostc')
            ->setToUrl('mysql://root:root@127.0.0.1:2206/sequelmovie2')
            ->setToHost('');


        if ($this->ssh($ssh) !== 'ssh -F ./.phpsu/config/ssh_config hosta') {
            throw new \Exception('ERROR');
        }
        if ($this->rsync($rsync) !== 'rsync -avz -e "ssh -F ./.phpsu/config/ssh_config" hosta:~/test/* ./__test/') {
            throw new \Exception('ERROR');
        }
        if ($this->database($database) !== 'ssh -F ./.phpsu/config/ssh_config hostc -C "mysqldump -hdatabase -P3306 -uroot -proot sequelmovie" | mysql -h127.0.0.1 -P2206 -uroot -proot sequelmovie2') {
            throw new \Exception('ERROR');
        }
    }

    private function rsync(RsyncCmd $rsync): string
    {
        file_put_contents('.phpsu/config/ssh_config', $rsync->getSshConfig()->toFileString());
        return 'rsync ' . $rsync->getOptions() . ' -e "ssh -F ./.phpsu/config/ssh_config" ' . $rsync->getFrom() . ' ' . $rsync->getTo();
    }

    private function ssh(SshCmd $ssh): string
    {
        file_put_contents('.phpsu/config/ssh_config', $ssh->getSshConfig()->toFileString());
        return 'ssh -F ./.phpsu/config/ssh_config ' . $ssh->getInto();
    }

    private function database(DatabaseCmd $database)
    {
        file_put_contents('.phpsu/config/ssh_config', $database->getSshConfig()->toFileString());
        $from = $this->parseDatabaseUrl($database->getFromUrl());
        $to = $this->parseDatabaseUrl($database->getToUrl());

        $dumpCmd = "mysqldump -h{$from['host']} -P{$from['port']} -u{$from['user']} -p{$from['pass']} {$from['path']}";
        if ($database->getFromHost()) {
            $dumpCmd = 'ssh -F ./.phpsu/config/ssh_config ' . $database->getFromHost() . ' -C "' . $dumpCmd . '"';
        }
        $importCmd = "mysql -h{$to['host']} -P{$to['port']} -u{$to['user']} -p{$to['pass']} {$to['path']}";
        if ($database->getToHost()) {
            $importCmd = 'ssh -F ./.phpsu/config/ssh_config ' . $database->getToHost() . ' -C "' . $importCmd . '"';
        }
        return $dumpCmd . ' | ' . $importCmd;
    }

    private function parseDatabaseUrl(string $url): array
    {
        $parsedUrl = parse_url($url);
        $parsedUrl = [
            'scheme' => $parsedUrl['scheme'] ?? 'mysql',
            'host' => $parsedUrl['host'] ?? die('host Not Set'),
            'port' => $parsedUrl['port'] ?? 3306,
            'user' => $parsedUrl['user'] ?? die('username Not Set'),
            'pass' => $parsedUrl['pass'] ?? die('password Not Set'),
            'path' => $parsedUrl['path'] ?? die('database Not Set'),
            'query' => $parsedUrl['query'] ?? '',
            'fragment' => $parsedUrl['fragment'] ?? '',
        ];
        $parsedUrl['path'] = str_replace('/', '', $parsedUrl['path']);
        return $parsedUrl;
    }
}
