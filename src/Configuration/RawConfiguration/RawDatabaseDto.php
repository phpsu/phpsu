<?php
declare(strict_types=1);

namespace PHPSu\Configuration\RawConfiguration;

use PHPSu\Configuration\Dto\NameableDto;

class RawDatabaseDto extends NameableDto
{
    protected $options;

    public function getOptions(): RawOptionBag
    {
    }
}
