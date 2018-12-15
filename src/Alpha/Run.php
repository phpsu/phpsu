<?php
declare(strict_types=1);

namespace PHPSu\Alpha;

final class Run
{
    public function __construct()
    {
        $sshConfig = new \stdClass();
        $sshConfig->hostc = new \stdClass();
        $sshConfig->hostc->User = 'user';
        $sshConfig->hostc->HostName = 'host_c';
        $sshConfig->hostc->ProxyJump = 'hostb';

        $sshConfig->hostb = new \stdClass();
        $sshConfig->hostb->User = 'user';
        $sshConfig->hostb->HostName = 'host_b';
        $sshConfig->hostb->ProxyJump = 'hosta';

        $sshConfig->hosta = new \stdClass();
        $sshConfig->hosta->User = 'user';
        $sshConfig->hosta->HostName = 'localhost';
        $sshConfig->hosta->Port = '2208';

        $sshConfig->{'*'} = new \stdClass();
        $sshConfig->{'*'}->StrictHostKeyChecking = 'no';
        $sshConfig->{'*'}->UserKnownHostsFile = '/dev/null';
        $sshConfig->{'*'}->IdentityFile = './docker/testCaseD/id_rsa';

        $ssh = new \stdClass();
        $ssh->into = 'hosta';
        $ssh->sshConfig = $sshConfig;

        $rsync = new \stdClass();
        $rsync->from = 'hosta:~/test/*';
        $rsync->to = './__test/';
        $rsync->options = '-avz';
        $rsync->sshConfig = $sshConfig;

        $database = new \stdClass();
        $database->fromHost = 'hostc';
        $database->from = 'mysql://root:root@database/sequelmovie';
        $database->toHost = '';
        $database->to = 'mysql://root:root@127.0.0.1:2206/sequelmovie2';
        $database->sshConfig = $sshConfig;

//        passthru($this->ssh($ssh));
//        passthru($this->rsync($rsync));
//        passthru($this->database($database));
    }

    private function rsync(\stdClass $rsync): string
    {
        file_put_contents('.phpsu/config/ssh_config', $this->sshConfig($rsync->sshConfig));
        return 'rsync ' . $rsync->options . ' -e "ssh -F ./.phpsu/config/ssh_config" ' . $rsync->from . ' ' . $rsync->to;
    }

    private function ssh(\stdClass $ssh): string
    {
        file_put_contents('.phpsu/config/ssh_config', $this->sshConfig($ssh->sshConfig));
        return 'ssh -F ./.phpsu/config/ssh_config ' . $ssh->into;
    }

    private function database(\stdClass $database)
    {
        file_put_contents('.phpsu/config/ssh_config', $this->sshConfig($database->sshConfig));
        $from = $this->parseDatabaseUrl($database->from);
        $to = $this->parseDatabaseUrl($database->to);

        $dumpCmd = "mysqldump -h{$from['host']} -P{$from['port']} -u{$from['user']} -p{$from['pass']} {$from['path']}";
        if ($database->fromHost) {
            $dumpCmd = 'ssh -F ./.phpsu/config/ssh_config ' . $database->fromHost . ' -C "' . $dumpCmd . '"';
        }
        $importCmd = "mysql -h{$to['host']} -P{$to['port']} -u{$to['user']} -p{$to['pass']} {$to['path']}";
        if ($database->toHost) {
            $importCmd = 'ssh -F ./.phpsu/config/ssh_config ' . $database->toHost . ' -C "' . $importCmd . '"';
        }
        return $dumpCmd . ' | ' . $importCmd;
    }

    private function sshConfig(\stdClass $sshConfig): string
    {
        $result = '';
        foreach ($sshConfig as $host => $config) {
            $config = (array)$config;
            $result .= 'Host ' . $host . PHP_EOL;
            ksort($config);
            foreach ($config as $key => $value) {
                $result .= '  ' . $key . ' ' . $value . PHP_EOL;
            }
            $result .= PHP_EOL;
        }
        return $result;
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
