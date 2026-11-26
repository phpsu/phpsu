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

    /** @var array<int, array{string, ?string[]}> path and the only hosts it applies to (null = all hosts) */
    private array $includes = [];

    private SplFileObject $file;

    public static function fromGlobal(GlobalConfig $global, string $currentHost): SshConfig
    {
        $sshConfig = (new SshConfigGenerator())->generate($global->getSshConnections(), $global->getDefaultSshConfig(), $currentHost);
        foreach ($global->getSshConfigFiles() as $sshConfigFile) {
            if ($sshConfigFile->getPath() !== null) {
                // a downloaded file must only change the hosts it defines itself (eg. no Host * for all phpsu hosts)
                $sshConfig->addInclude($sshConfigFile->getPath(), $sshConfigFile->isUrl() ? $sshConfigFile->getHosts() : null);
            }
        }

        return $sshConfig;
    }

    /**
     * @param null|string[] $onlyForHosts the include only applies to these hosts, null for all hosts
     */
    public function addInclude(string $path, ?array $onlyForHosts = null): void
    {
        $this->includes[] = [$path, $onlyForHosts];
    }

    /**
     * @return string[]
     */
    public function getIncludes(): array
    {
        return array_column($this->includes, 0);
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

        // a Match line ends the previous Host block, otherwise the Includes would only apply to that Host
        $previousMatch = null;
        foreach ($this->includes as [$path, $onlyForHosts]) {
            $match = $this->getIncludeMatch($onlyForHosts);
            if ($match === null) {
                continue;
            }

            if ($match !== $previousMatch) {
                $result .= ($previousMatch !== null ? PHP_EOL : '') . $match . PHP_EOL;
                $previousMatch = $match;
            }

            $result .= '  Include ' . $this->escapeIncludePath($path) . PHP_EOL;
        }

        if ($previousMatch !== null) {
            $result .= PHP_EOL;
        }

        if ($defaultHost instanceof SshConfigHost) {
            $result .= $this->hostToString('*', $defaultHost);
        }

        return $result;
    }

    /**
     * @param null|string[] $onlyForHosts
     */
    private function getIncludeMatch(?array $onlyForHosts): ?string
    {
        if ($onlyForHosts === null) {
            return 'Match all';
        }

        // originalhost is the host given to ssh, it can not be changed by the included file (unlike host via HostName)
        $hosts = array_filter($onlyForHosts, static fn(string $host): bool => (bool)preg_match('/^[A-Za-z0-9._-]+$/', $host));
        return $hosts === [] ? null : 'Match originalhost ' . implode(',', $hosts);
    }

    /**
     * ssh expands Include paths with glob(3), so glob characters, quotes and backslashes are escaped
     */
    private function escapeIncludePath(string $path): string
    {
        $escaped = str_replace(['\\', '"'], ['\\\\\\\\', '\\"'], $path);
        $escaped = (string)preg_replace('/([*?\[\]])/', '\\\\$1', $escaped);
        return $escaped === $path && !preg_match('/\s/', $path) ? $path : '"' . $escaped . '"';
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
