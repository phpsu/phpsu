<?php
declare(strict_types=1);

namespace PHPSu\Alpha;

final class GlobalConfig
{
    /** @var SshConnections */
    private $sshConnections;

    /** @var AppInstances */
    private $appInstances;

    /** @var FileSystems */
    private $fileSystems;

    /** @var Databases */
    private $databases;

    public function __construct()
    {
        $this->sshConnections = new SshConnections();
        $this->appInstances = new AppInstances();
        $this->fileSystems = new FileSystems();
        $this->databases = new Databases();
    }

    public function addSshConnection(SshConnection $sshConnection): GlobalConfig
    {
        $this->sshConnections->add($sshConnection);
        return $this;
    }

    public function addAppInstance(AppInstance $appInstance): GlobalConfig
    {
        $this->appInstances->add($appInstance);
        return $this;
    }

    public function addFilesystem(FileSystem $fileSystem): GlobalConfig
    {
        $this->fileSystems->add($fileSystem);
        return $this;
    }

    public function addDatabase(Database $database): GlobalConfig
    {
        $this->databases->add($database);
        return $this;
    }

    /**
     * @return FileSystem[]
     */
    public function getFileSystems(): array
    {
        return $this->fileSystems->getAll();
    }

    /**
     * @return Database[]
     */
    public function getDatabases(): array
    {
        return $this->databases->getAll();
    }

    /**
     * @param string $host
     * @throws \Exception
     */
    public function validateConnectionToHost(string $host): void
    {
        $this->sshConnections->getPossibilities($host);
    }

    public function getSshConnections(): SshConnections
    {
        return $this->sshConnections;
    }

    public function getAppInstance(string $appName): AppInstance
    {
        return $this->appInstances->get($appName);
    }

    public function getHostName(string $connectionName): string
    {
        if ($this->appInstances->has($connectionName)) {
            return $this->getAppInstance($connectionName)->getHost();
        }
        return $connectionName;
    }
}
