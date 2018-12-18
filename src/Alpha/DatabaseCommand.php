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

    /**
     * @param GlobalConfig $global
     * @param string $fromInstanceName
     * @param string $toInstanceName
     * @param string $currentHost
     * @return DatabaseCommand[]
     */
    public static function fromGlobal(GlobalConfig $global, string $fromInstanceName, string $toInstanceName, string $currentHost): array
    {
        $fromInstance = $global->getAppInstance($fromInstanceName);
        $toInstance = $global->getAppInstance($toInstanceName);
        $result = [];
        foreach ($global->getDatabases() as $databaseName => $databaseDSN) {
            $result[] = static::fromAppInstances($fromInstance, $toInstance, $databaseDSN, $currentHost);
        }
        return $result;
    }

    public static function fromAppInstances(AppInstance $from, AppInstance $to, Database $database, string $currentHost): DatabaseCommand
    {
        $result = new static();
        $result->fromHost = $from->getHost() === $currentHost ? '' : $from->getHost();
        $result->toHost = $to->getHost() === $currentHost ? '' : $to->getHost();
        $result->fromUrl = $database->getUrl();
        $result->toUrl = $database->getUrl();
        return $result;
    }

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

    public function generate(): string
    {
        $hostsDifferentiate = $this->fromHost !== $this->toHost;
        $from = $this->parseDatabaseUrl($this->fromUrl);
        $to = $this->parseDatabaseUrl($this->toUrl);

        $dumpCmd = "mysqldump -h{$from['host']} -P{$from['port']} -u{$from['user']} -p{$from['pass']} {$from['path']}";
        $importCmd = "mysql -h{$to['host']} -P{$to['port']} -u{$to['user']} -p{$to['pass']} {$to['path']}";
        if ($hostsDifferentiate) {
            if ($this->fromHost) {
                $sshCommand = new SshCommand();
                $sshCommand->setSshConfig($this->sshConfig);
                $sshCommand->setInto($this->fromHost);
                $dumpCmd = $sshCommand->generate($dumpCmd);
            }
            if ($this->toHost) {
                $sshCommand = new SshCommand();
                $sshCommand->setSshConfig($this->sshConfig);
                $sshCommand->setInto($this->toHost);
                $importCmd = $sshCommand->generate($importCmd);
            }
            return $dumpCmd . ' | ' . $importCmd;
        }
        $sshCommand = new SshCommand();
        $sshCommand->setSshConfig($this->sshConfig);
        $sshCommand->setInto($this->fromHost);
        return $sshCommand->generate($dumpCmd . ' | ' . $importCmd);
    }

    private function parseDatabaseUrl(string $url): array
    {
        //TODO: make compatible with PDO_MYSQL DSN: http://php.net/manual/de/ref.pdo-mysql.connection.php
        //TODO: use DSN Class
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
