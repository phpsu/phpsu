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

    /**
     * @param string|array $excludes
     * @return Database
     */
    public function addExcludes($excludes): Database
    {
        if (is_array($excludes)) {
            $this->excludes = array_merge($this->excludes, $excludes);
        } else {
            $this->excludes[] = $excludes;
        }
        return $this;
    }

    /**
     * @param string $exclude
     * @return Database
     * @deprecated Use $this->addExcludes();
     */
    public function addExclude(string $exclude): Database
    {
        $this->addExcludes($exclude);
        return $this;
    }
}
