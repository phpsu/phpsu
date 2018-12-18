<?php
declare(strict_types=1);

namespace PHPSu\Alpha;

final class Database
{
    /** @var string */
    private $name;

    /** @var string */
    private $url;

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
}
