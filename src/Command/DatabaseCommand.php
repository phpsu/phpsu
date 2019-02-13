<?php
declare(strict_types=1);

namespace PHPSu\Command;

use PHPSu\Config\AppInstance;
use PHPSu\Config\Database;
use PHPSu\Config\GlobalConfig;
use PHPSu\Config\SshConfig;

final class DatabaseCommand implements CommandInterface
{
    /** @var string */
    private $name;
    /** @var SshConfig */
    private $sshConfig;
    /** @var string[] */
    private $excludes = [];

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
    public static function fromGlobal(GlobalConfig $global, string $fromInstanceName, string $toInstanceName, string $currentHost, bool $all): array
    {
        $fromInstance = $global->getAppInstance($fromInstanceName);
        $toInstance = $global->getAppInstance($toInstanceName);
        $result = [];
        foreach ($global->getDatabases() as $databaseName => $database) {
            $fromDatabase = $database;
            if ($fromInstance->hasDatabase($databaseName)) {
                $fromDatabase = $fromInstance->getDatabase($databaseName);
            }
            $toDatabase = $database;
            if ($toInstance->hasDatabase($databaseName)) {
                $toDatabase = $toInstance->getDatabase($databaseName);
            }
            $result[] = static::fromAppInstances($fromInstance, $toInstance, $fromDatabase, $toDatabase, $currentHost, $all);
        }
        foreach ($fromInstance->getDatabases() as $databaseName => $fromDatabase) {
            if ($toInstance->hasDatabase($databaseName)) {
                $toDatabase = $toInstance->getDatabase($databaseName);
                $result[] = static::fromAppInstances($fromInstance, $toInstance, $fromDatabase, $toDatabase, $currentHost, $all);
            }
        }
        return $result;
    }

    public static function fromAppInstances(AppInstance $from, AppInstance $to, Database $fromDatabase, Database $toDatabase, string $currentHost, bool $all): DatabaseCommand
    {
        $result = new static();
        $result->setName('database:' . $fromDatabase->getName());
        $result->setFromHost($from->getHost() === $currentHost ? '' : $from->getHost());
        $result->setToHost($to->getHost() === $currentHost ? '' : $to->getHost());
        $result->setFromUrl($fromDatabase->getUrl());
        $result->setToUrl($toDatabase->getUrl());
        if ($all === false) {
            $result->setExcludes(array_unique(array_merge($fromDatabase->getExcludes(), $toDatabase->getExcludes())));
        }
        return $result;
    }


    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): DatabaseCommand
    {
        $this->name = $name;
        return $this;
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

    /**
     * @return string[]
     */
    public function getExcludes(): array
    {
        return $this->excludes;
    }

    /**
     * @param string[] $excludes
     * @return DatabaseCommand
     */
    public function setExcludes(array $excludes): DatabaseCommand
    {
        $this->excludes = $excludes;
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
        $hostsDifferentiate = $this->getFromHost() !== $this->getToHost();
        $from = $this->parseDatabaseUrl($this->getFromUrl());
        $to = $this->parseDatabaseUrl($this->getToUrl());

        $dumpCmd = 'mysqldump --opt --skip-comments ' . $this->generateCliParameters($from) . $this->excludeParts($from['path']);
        $importCmd = 'mysql ' . $this->generateCliParameters($to);
        if ($hostsDifferentiate) {
            if ($this->getFromHost()) {
                $sshCommand = new SshCommand();
                $sshCommand->setSshConfig($this->getSshConfig());
                $sshCommand->setInto($this->getFromHost());
                $dumpCmd = $sshCommand->generate($dumpCmd);
            }
            if ($this->getToHost()) {
                $sshCommand = new SshCommand();
                $sshCommand->setSshConfig($this->getSshConfig());
                $sshCommand->setInto($this->getToHost());
                $importCmd = $sshCommand->generate($importCmd);
            }
            return $dumpCmd . ' | ' . $importCmd;
        }
        $sshCommand = new SshCommand();
        $sshCommand->setSshConfig($this->getSshConfig());
        $sshCommand->setInto($this->getFromHost());
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

    /**
     * @param array $parameters
     * @return string
     */
    private function generateCliParameters(array $parameters): string
    {
        $result = [];
        $result[] = '-h' . escapeshellarg($parameters['host']);
        if ((int)$parameters['port'] !== 3306) {
            $result[] = '-P' . (int)$parameters['port'];
        }
        $result[] = '-u' . escapeshellarg($parameters['user']);
        $result[] = '-p' . escapeshellarg($parameters['pass']);
        $result[] = '' . escapeshellarg($parameters['path']);
        return implode(' ', $result);
    }

    private function excludeParts(string $database): string
    {
        $excludeOptions = [];
        foreach ($this->getExcludes() as $exclude) {
            $excludeOptions[] = '--ignore-table=' . escapeshellarg($database . '.' . $exclude);
        }
        if ($excludeOptions) {
            return ' ' . implode(' ', $excludeOptions);
        }
        return '';
    }
}
