<?php
declare(strict_types=1);

namespace PHPSu\Config;

/**
 * @property string User
 * @property string HostName
 * @property int|string Port
 * @property string ProxyJump
 */
final class SshConfigHost
{
    /** @var string[] */
    private $options = [];

    public function __isset(string $name): bool
    {
        return isset($this->options[$name]);
    }

    public function __get(string $name): string
    {
        return $this->options[$name];
    }

    public function __set(string $name, string $config): void
    {
        $this->options[$name] = $config;
    }

    public function getConfig(): array
    {
        ksort($this->options);
        return $this->options;
    }
}
