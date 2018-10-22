<?php
declare(strict_types=1);

namespace PHPSu\Configuration\Dto;

class FilesystemDto extends NameableDto
{
    protected $options;

    public function getOptions(): OptionBag
    {
    }
}
