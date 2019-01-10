<?php
declare(strict_types=1);

namespace PHPSu\Config;

final class AppInstances
{
    /** @var AppInstance[] */
    private $instances;

    public function add(AppInstance $appInstance): AppInstances
    {
        $this->instances[$appInstance->getName()] = $appInstance;
        return $this;
    }

    public function has(string $name): bool
    {
        return isset($this->instances[$name]);
    }

    public function get(string $name): AppInstance
    {
        if (!isset($this->instances[$name])) {
            throw new \Exception(sprintf('App Instance with name %s not found', $name));
        }
        return $this->instances[$name];
    }

    /**
     * @return AppInstance[]
     */
    public function getAll(): array
    {
        return $this->instances;
    }
}
