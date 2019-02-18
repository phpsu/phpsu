<?php
declare(strict_types=1);

namespace PHPSu\Config;

final class Database
{
    /** @var string */
    private $name;

    /** @var string */
    private $url;

    /** @var string[] */
    private $excludes = [];

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): Database
    {
        $this->name = $name;
        return $this;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): Database
    {
        $this->url = $url;
        return $this;
    }

    public function getExcludes(): array
    {
        return $this->excludes;
    }

    public function addExcludes(array $excludes): Database
    {
        foreach ($excludes as $exclude) {
            $this->addExclude($exclude);
        }
        return $this;
    }

    public function addExclude(string $exclude): Database
    {
        $this->excludes[] = $exclude;
        return $this;
    }
}
