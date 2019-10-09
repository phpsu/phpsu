<?php

declare(strict_types=1);

namespace PHPSu\Config;

final class SshConfig
{
    /** @var SshConfigHost[] */
    private $hosts = [];

    /** @var \SplFileObject */
    private $file;

    public static function fromGlobal(GlobalConfig $global, string $currentHost): SshConfig
    {
        return (new SshConfigGenerator())->generate($global->getSshConnections(), $global->getDefaultSshConfig(), $currentHost);
    }

    public function __isset(string $name): bool
    {
        return isset($this->hosts[$name]);
    }

    public function __get(string $name): SshConfigHost
    {
        return $this->hosts[$name];
    }

    /**
     * @return void
     */
    public function __set(string $name, SshConfigHost $host)
    {
        $this->hosts[$name] = $host;
    }

    public function getFile(): \SplFileObject
    {
        return $this->file;
    }

    /**
     * @return void
     */
    public function setFile(\SplFileObject $file)
    {
        $this->file = $file;
    }

    /**
     * @return void
     */
    public function writeConfig()
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
