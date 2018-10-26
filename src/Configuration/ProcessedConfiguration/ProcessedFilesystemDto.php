<?php
declare(strict_types=1);

namespace PHPSu\Configuration\ProcessedConfiguration;

use PHPSu\Configuration\ProcessedConfiguration\AbstractClasses\ProcessedTypeableDto;

class ProcessedFilesystemDto extends ProcessedTypeableDto
{
    /**
     * @var ProcessedOptionBag
     */
    protected $options;

    public function __construct(string $name, string $type, ProcessedOptionBag $options = null)
    {
        parent::__construct($name, $type);
        $this->options = $options ?? new ProcessedOptionBag();
    }

    public function getOptions(): ProcessedOptionBag
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
