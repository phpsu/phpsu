<?php
declare(strict_types=1);

namespace PHPSu\Alpha;

final class AppInstances
{
    /** @var AppInstance[] */
    private $instances;

    public function add(AppInstance $appInstance): AppInstances
    {
        $this->instances[$appInstance->getName()] = $appInstance;
        return $this;
    }

    /**
     * @param string $name
     * @return AppInstance
     * @throws AppInstanceNotFoundException
     */
    public function get(string $name): AppInstance
    {
        if (!isset($this->instances[$name])) {
            throw new class(sprintf('App Instance with name %s not found', $name)) extends \Exception implements AppInstanceNotFoundException
            {
            };
        }
        return $this->instances[$name];
    }
}
