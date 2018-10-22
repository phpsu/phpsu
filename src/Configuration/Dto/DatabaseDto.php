<?php
declare(strict_types=1);

namespace PHPSu\Configuration\Dto;

class DatabaseDto extends NameableDto
{
    protected $options;

    public function getOptions(): OptionBag
    {
    }
}
