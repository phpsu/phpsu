<?php
declare(strict_types=1);

namespace PHPSu\Config;

use PHPSu\Exceptions\ConfigurationException;

final class SshConnection implements ConnectionInterface
{
    /** @var string */
    private $host;
    /** @var SshUrl */
    private $url;
    /** @var string[] */
    private $options = [];
    /** @var string[] */
    private $from = [];
    /** @var AppInstance[] */
    private $connectsTo = [];
    /** @var string */
    private $name;

    public function setName(string $name): SshConnection
    {
        $this->name = $name;
        return $this;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function setHost(string $host): SshConnection
    {
        if (strpos($host, '/') !== false) {
            throw new \InvalidArgumentException(sprintf('host %s has invalid character', $host));
        }
        $this->host = $host;
        return $this;
    }

    public function getUrl(): SshUrl
    {
        return $this->url;
    }

    public function setUrl(string $url): SshConnection
    {
        $this->url = new SshUrl($url);
        return $this;
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
     * @return SshConnection
     */
    public function setOptions(array $options): SshConnection
    {
        $this->options = $options;
        return $this;
    }

    /**
     * @return string[]
     */
    public function getFrom(): array
    {
        return $this->from;
    }

    /**
     * @param string[] $from
     * @return SshConnection
     */
    public function setFrom(array $from): SshConnection
    {
        $this->from = $from;
        return $this;
    }

    /**
     * @return AppInstance[]
     */
    public function getConnectingInstances(): array
    {
        return $this->connectsTo;
    }

    /**
     * @param AppInstance[] $connectingInstances
     * @return void
     */
    public function connectsTo(...$connectingInstances)
    {
        foreach ($connectingInstances as $instance) {
            if (!($instance instanceof AppInstance)) {
                throw new ConfigurationException('ConnectsTo requires all arguments to be AppInstances');
            }
            $this->connectsTo[] = $instance;
        }
    }
}
