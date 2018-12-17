<?php
declare(strict_types=1);

namespace PHPSu\Alpha;

final class DatabaseCommand implements CommandInterface
{
    /** @var SshConfig */
    private $sshConfig;

    /** @var string */
    private $fromUrl;
    /** @var string */
    private $fromHost;

    /** @var string */
    private $toUrl;
    /** @var string */
    private $toHost;

    public function getSshConfig(): SshConfig
    {
        return $this->sshConfig;
    }

    public function setSshConfig(SshConfig $sshConfig): DatabaseCommand
    {
        $this->sshConfig = $sshConfig;
        return $this;
    }

    public function getFromUrl(): string
    {
        return $this->fromUrl;
    }

    public function setFromUrl(string $fromUrl): DatabaseCommand
    {
        $this->fromUrl = $fromUrl;
        return $this;
    }

    public function getFromHost(): string
    {
        return $this->fromHost;
    }

    public function setFromHost(string $fromHost): DatabaseCommand
    {
        $this->fromHost = $fromHost;
        return $this;
    }

    public function getToUrl(): string
    {
        return $this->toUrl;
    }

    public function setToUrl(string $toUrl): DatabaseCommand
    {
        $this->toUrl = $toUrl;
        return $this;
    }

    public function getToHost(): string
    {
        return $this->toHost;
    }

    public function setToHost(string $toHost): DatabaseCommand
    {
        $this->toHost = $toHost;
        return $this;
    }

    public function generate():string
    {
        $this->sshConfig->writeConfig($file = new TempSshConfigFile());
        $from = $this->parseDatabaseUrl($this->fromUrl);
        $to = $this->parseDatabaseUrl($this->toUrl);

        $dumpCmd = "mysqldump -h{$from['host']} -P{$from['port']} -u{$from['user']} -p{$from['pass']} {$from['path']}";
        if ($this->fromHost) {
            $dumpCmd = 'ssh -F ' . $file->getPathname() . ' ' . $this->fromHost . ' -C "' . $dumpCmd . '"';
        }
        $importCmd = "mysql -h{$to['host']} -P{$to['port']} -u{$to['user']} -p{$to['pass']} {$to['path']}";
        if ($this->toHost) {
            $importCmd = 'ssh -F ' . $file->getPathname() . ' ' . $this->toHost . ' -C "' . $importCmd . '"';
        }
        return $dumpCmd . ' | ' . $importCmd;
    }

    private function parseDatabaseUrl(string $url): array
    {
        //TODO: make compatible with PDO_MYSQL DSN: http://php.net/manual/de/ref.pdo-mysql.connection.php
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
