<?php
declare(strict_types=1);

namespace PHPSu\Config;

final class DatabaseConnection implements ConnectionInterface
{
    /** @var string $identifier */
    private $identifier;
    /** @var AppInstance $belongsToAppInstance */
    private $belongsToAppInstance;
    /** @var DatabaseConfigurationInterface $connectionInformation */
    private $connectionInformation;
    /** @var array $excludes */
    private $excludes = [];
    /** @var array $options */
    private $options = [];

    public function setIdentifier(string $identifier): DatabaseConnection
    {
        $this->identifier = $identifier;
        return $this;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * @return string[]
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @param string[] $options
     * @return DatabaseConnection
     */
    public function setOptions(array $options): DatabaseConnection
    {
        $this->options = $options;
        return $this;
    }

    public static function fromDatabaseObject(Database $databaseObject): DatabaseConnection
    {
        $connection = new self;
        $connection->setIdentifier($databaseObject->getName());
        // the former DatabaseObject was only able to contain mysql-urls, therefore a special parsing is not necessary
        $information = new SqlDatabaseConfiguration($databaseObject->getUrl());
        $connection->setConnectionInformation($information);
        $connection->setExcludes($databaseObject->getExcludes());
        return $connection;
    }

    /**
     * @param AppInstance $belongsToAppInstance
     * @return DatabaseConnection
     */
    public function setBelongsToAppInstance(AppInstance $belongsToAppInstance): DatabaseConnection
    {
        $this->belongsToAppInstance = $belongsToAppInstance;
        return $this;
    }

    /**
     * @return AppInstance
     */
    public function getBelongsToAppInstance(): AppInstance
    {
        return $this->belongsToAppInstance;
    }

    /**
     * @param DatabaseConfigurationInterface $connectionInformation
     * @return DatabaseConnection
     */
    public function setConnectionInformation(DatabaseConfigurationInterface $connectionInformation): DatabaseConnection
    {
        $this->connectionInformation = $connectionInformation;
        return $this;
    }

    /**
     * @return DatabaseConfigurationInterface
     */
    public function getConnectionInformation(): DatabaseConfigurationInterface
    {
        return $this->connectionInformation;
    }

    /**
     * @param array $excludes
     * @return DatabaseConnection
     */
    public function setExcludes(array $excludes): DatabaseConnection
    {
        $this->excludes = $excludes;
        return $this;
    }

    /**
     * @return array
     */
    public function getExcludes(): array
    {
        return $this->excludes;
    }
}
