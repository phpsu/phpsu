<?php

declare(strict_types=1);

namespace PHPSu\Config;

use RuntimeException;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * resolves local ssh config files and downloads/caches/sanitizes remote ones
 * @internal
 */
final readonly class SshConfigFileLoader
{
    private string $cacheDirectory;

    public function __construct(
        ?string $cacheDirectory = null,
        private SshConfigDownloaderInterface $downloader = new SshConfigDownloader(),
        private SshConfigFileParser $parser = new SshConfigFileParser(),
    ) {
        $this->cacheDirectory = $cacheDirectory ?? getcwd() . '/.phpsu/ssh-configs';
    }

    public function getCacheDirectory(): string
    {
        return $this->cacheDirectory;
    }

    public function loadAll(GlobalConfig $config, OutputInterface $output, bool $forceRefresh = false): void
    {
        if ($output instanceof ConsoleOutputInterface) {
            $output = $output->getErrorOutput();
        }

        foreach ($config->getSshConfigFiles() as $sshConfigFile) {
            if ($sshConfigFile->isLoaded()) {
                continue;
            }

            if ($sshConfigFile->isUrl()) {
                $this->loadUrl($sshConfigFile, $output, $forceRefresh);
            } else {
                $this->loadLocal($sshConfigFile, $config->getConfigDirectory());
            }

            $config->getSshConnections()->addExternalHosts(...$sshConfigFile->getHosts());
        }
    }

    private function loadLocal(SshConfigFile $sshConfigFile, string $configDirectory): void
    {
        $path = $this->resolveLocalPath($sshConfigFile->getSource(), $configDirectory);
        if (!is_file($path) || !is_readable($path)) {
            throw new RuntimeException(sprintf('ssh config file %s (%s) does not exist or is not readable', $sshConfigFile->getSource(), $path));
        }

        $sshConfigFile->setLoaded($path, $this->parser->extractHosts((string)file_get_contents($path)));
    }

    private function resolveLocalPath(string $source, string $configDirectory): string
    {
        if ($source === '~' || str_starts_with($source, '~/')) {
            $home = getenv('HOME');
            if (!is_string($home) || $home === '') {
                throw new RuntimeException(sprintf('could not resolve %s: HOME is not set', $source));
            }

            return rtrim($home, '/') . substr($source, 1);
        }

        if (str_starts_with($source, '/')) {
            return $source;
        }

        return rtrim($configDirectory, '/') . '/' . $source;
    }

    private function loadUrl(SshConfigFile $sshConfigFile, OutputInterface $output, bool $forceRefresh): void
    {
        [$url, $username, $password] = $this->extractCredentials($sshConfigFile);
        $cacheFile = $this->cacheDirectory . '/' . $this->getCacheFileName($url);
        $isCached = is_file($cacheFile);
        if ($isCached && !$forceRefresh && time() - (int)filemtime($cacheFile) < $sshConfigFile->getTtl()) {
            $sshConfigFile->setLoaded($cacheFile, $this->parser->extractHosts((string)file_get_contents($cacheFile)));
            return;
        }

        try {
            $content = $this->downloader->download($url, $username, $password);
        } catch (RuntimeException $runtimeException) {
            if ($isCached) {
                $output->writeln(sprintf('<comment>Warning: %s, using cached version from %s</comment>', $runtimeException->getMessage(), date('Y-m-d H:i:s', (int)filemtime($cacheFile))));
                $sshConfigFile->setLoaded($cacheFile, $this->parser->extractHosts((string)file_get_contents($cacheFile)));
                return;
            }

            $output->writeln(sprintf('<comment>Warning: %s, the hosts of this ssh config file are not available</comment>', $runtimeException->getMessage()));
            $sshConfigFile->setLoaded(null, []);
            return;
        }

        $sanitized = $this->parser->sanitize($content);
        foreach ($sanitized['removed'] as $removedLine) {
            $output->writeln(sprintf('<comment>removed from ssh config %s: %s</comment>', $url, $removedLine), OutputInterface::VERBOSITY_VERBOSE);
        }

        $this->writeCacheFile($cacheFile, $url, $sanitized['content'], $sanitized['removed']);
        $sshConfigFile->setLoaded($cacheFile, $this->parser->extractHosts($sanitized['content']));
    }

    /**
     * @return array{string, ?string, ?string}
     */
    private function extractCredentials(SshConfigFile $sshConfigFile): array
    {
        $url = $sshConfigFile->getSource();
        $username = $sshConfigFile->getUsername();
        $password = $sshConfigFile->getPassword();
        $parts = parse_url($url);
        if (is_array($parts) && isset($parts['user'])) {
            $url = (string)preg_replace('#^(https://)[^@/]*@#i', '$1', $url);
            if ($username === null) {
                $username = rawurldecode($parts['user']);
                $password = rawurldecode($parts['pass'] ?? '');
            }
        }

        return [$url, $username, $password];
    }

    /**
     * human readable name + short hash, eg. example.com-ssh-config-1a2b3c4d.conf
     */
    public function getCacheFileName(string $url): string
    {
        $parts = parse_url($url);
        $readable = ($parts['host'] ?? '') . ($parts['path'] ?? '');
        $readable = strtolower(trim((string)preg_replace('/[^a-z0-9.]+/i', '-', $readable), '-.'));
        $readable = rtrim(substr($readable, 0, 60), '-.');
        return ($readable !== '' ? $readable . '-' : '') . substr(sha1($url), 0, 8) . '.conf';
    }

    /**
     * @param string[] $removed
     */
    private function writeCacheFile(string $cacheFile, string $url, string $content, array $removed): void
    {
        $directory = dirname($cacheFile);
        if (!is_dir($directory) && !@mkdir($directory, 0777, true) && !is_dir($directory)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $directory));
        }

        $header = '# downloaded by phpsu from ' . $url . ' at ' . date('Y-m-d H:i:s') . PHP_EOL;
        foreach ($removed as $removedLine) {
            $header .= '# removed by phpsu: ' . $removedLine . PHP_EOL;
        }

        $tempFile = $cacheFile . '.' . getmypid() . '.tmp';
        if (@file_put_contents($tempFile, $header . PHP_EOL . $content . PHP_EOL) === false) {
            throw new RuntimeException(sprintf('could not write ssh config cache file %s', $cacheFile));
        }

        // ssh refuses config files that are writable by others
        chmod($tempFile, 0600);
        rename($tempFile, $cacheFile);
    }
}
