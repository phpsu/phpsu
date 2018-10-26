<?php
declare(strict_types=1);

namespace PHPSu\Configuration\RawConfiguration;

use PHPSu\Configuration\RawConfiguration\AbstractClasses\RawTypeableDto;

class RawFilesystemDto extends RawTypeableDto
{
    /**
     * @var RawOptionBag
     */
    protected $options;

    public function __construct(string $name = '', string $type = '', RawOptionBag $options = null)
    {
        parent::__construct($name, $type);
        $this->options = $options ?? new RawOptionBag();
    }

    public function getOptions(): RawOptionBag
    {
        return $this->options;
    }

    public static function __set_state(array $data)
    {
        return new static(
            $data['name'] ?? '',
            $data['type'] ?? '',
            $data['options'] ?? []
        );
    }
}
