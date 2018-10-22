<?php
declare(strict_types=1);

namespace PHPSu\Core;

trait ApplicationContextAwareTrait
{
    /**
     * @var ApplicationContext
     */
    protected $context;

    public function injectContext(ApplicationContext $context)
    {
        $this->context = $context;
    }
}
