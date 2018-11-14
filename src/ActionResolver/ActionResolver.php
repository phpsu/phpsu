<?php

namespace PHPSu\ActionResolver;

use PHPSu\Actions\ActionList;
use PHPSu\Configuration\ProcessedConfiguration\ProcessedConfigurationDto;
use PHPSu\Core\ApplicationContext;

class ActionResolver
{
    /**
     * @var ApplicationContext
     */
    private $applicationContext;

    public function __construct(ApplicationContext $applicationContext)
    {
        $this->applicationContext = $applicationContext;
    }

    public function resolve(ProcessedConfigurationDto $configuration)
    {
        $fromHost = $configuration->getHosts()->offsetGet($this->applicationContext->fromHost);
        $toHost = $configuration->getHosts()->offsetGet($this->applicationContext->toHost);
        return new ActionList($fromHost, $toHost);
    }
}
