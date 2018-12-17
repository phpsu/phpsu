<?php
declare(strict_types=1);

namespace PHPSu\Alpha;

final class SshConfig
{
    private $hosts = [];

    public function __isset(string $name): bool
    {
        return isset($this->hosts[$name]);
    }

    public function __get(string $name): SshConfigHost
    {
        return $this->hosts[$name];
    }

    public function __set(string $name, SshConfigHost $host): void
    {
        $this->hosts[$name] = $host;
    }

    public function writeConfig(): void
    {
        file_put_contents('.phpsu/config/ssh_config', $this->toFileString()) or die('ERROR');
    }

    private function toFileString(): string
    {
        $result = '';
        foreach ($this->hosts as $host => $config) {
            $result .= 'Host ' . $host . PHP_EOL;
            foreach ($config->getConfig() as $key => $value) {
                $result .= '  ' . $key . ' ' . $value . PHP_EOL;
            }
            $result .= PHP_EOL;
        }
        return $result;
    }
}