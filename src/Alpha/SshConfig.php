<?php
declare(strict_types=1);

namespace PHPSu\Alpha;

final class SshConfig
{
    /** @var SshConfigHost[] */
    private $hosts = [];

    /** @var \SplFileObject */
    private $file;

    public static function fromGlobal(GlobalConfig $global, string $currentHost): SshConfig
    {
        return (new SshConfigGenerator())->generate($global->getSshConnections(), $currentHost);
    }

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

    public function getFile(): \SplFileObject
    {
        return $this->file;
    }

    public function setFile(\SplFileObject $file): void
    {
        $this->file = $file;
    }

    public function writeConfig(): void
    {
        $this->file->ftruncate(0);
        $this->file->fwrite($this->toFileString());
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
