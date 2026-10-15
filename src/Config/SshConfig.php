<?php

declare(strict_types=1);

namespace PHPSu\Config;

use SplFileObject;

/**
 * @internal
 */
final class SshConfig
{
    /** @var SshConfigHost[] */
    private array $hosts = [];

    /** @var string[] */
    private array $includes = [];

    private SplFileObject $file;

    public static function fromGlobal(GlobalConfig $global, string $currentHost): SshConfig
    {
        $sshConfig = (new SshConfigGenerator())->generate($global->getSshConnections(), $global->getDefaultSshConfig(), $currentHost);
        foreach ($global->getSshConfigFiles() as $sshConfigFile) {
            if ($sshConfigFile->getPath() !== null) {
                $sshConfig->addInclude($sshConfigFile->getPath());
            }
        }

        return $sshConfig;
    }

    public function addInclude(string $path): void
    {
        $this->includes[] = $path;
    }

    /**
     * @return string[]
     */
    public function getIncludes(): array
    {
        return $this->includes;
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

    public function getFile(): SplFileObject
    {
        return $this->file;
    }


    public function setFile(SplFileObject $file): void
    {
        $this->file = $file;
    }


    public function writeConfig(): void
    {
        $this->file->ftruncate(0);
        $this->file->fwrite($this->toFileString());
    }

    /**
     * ssh uses the first obtained value, so the order is: SshConnections, included ssh config files, default config (Host *)
     */
    private function toFileString(): string
    {
        $hosts = $this->hosts;
        $defaultHost = $hosts['*'] ?? null;
        unset($hosts['*']);

        $result = '';
        foreach ($hosts as $host => $config) {
            $result .= $this->hostToString((string)$host, $config);
        }

        if ($this->includes !== []) {
            // Match all ends the previous Host block, otherwise the Includes would only apply to that Host
            $result .= 'Match all' . PHP_EOL;
            foreach ($this->includes as $include) {
                $result .= '  Include ' . (preg_match('/\s/', $include) ? '"' . $include . '"' : $include) . PHP_EOL;
            }

            $result .= PHP_EOL;
        }

        if ($defaultHost instanceof SshConfigHost) {
            $result .= $this->hostToString('*', $defaultHost);
        }

        return $result;
    }

    private function hostToString(string $host, SshConfigHost $config): string
    {
        $result = 'Host ' . $host . PHP_EOL;
        foreach ($config->getConfig() as $key => $value) {
            $result .= '  ' . $key . ' ' . $value . PHP_EOL;
        }

        return $result . PHP_EOL;
    }
}
