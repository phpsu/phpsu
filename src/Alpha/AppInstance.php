<?php
declare(strict_types=1);

namespace PHPSu\Alpha;

final class AppInstance
{
    /** @var string */
    private $name;
    /** @var string */
    private $host;
    /** @var string */
    private $path;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): AppInstance
    {
        $this->name = $name;
        return $this;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function setHost(string $host): AppInstance
    {
        $this->host = $host;
        return $this;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function setPath(string $path): AppInstance
    {
        $this->path = $path;
        return $this;
    }
}
