<?php

declare(strict_types=1);

namespace PHPSu\Tests\Config;

use PHPSu\Config\GlobalConfig;
use PHPSu\Config\SshConfigFileLoader;
use PHPSu\Tests\TestHelper\BufferedConsoleOutput;
use PHPSu\Tests\TestHelper\FakeSshConfigDownloader;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;

final class SshConfigFileLoaderTest extends TestCase
{
    private string $directory;

    private string $oldHome;

    protected function setUp(): void
    {
        $this->directory = sys_get_temp_dir() . '/phpsu-ssh-config-file-loader-test-' . getmypid();
        mkdir($this->directory . '/config/ssh', 0777, true);
        mkdir($this->directory . '/home/.ssh', 0777, true);
        file_put_contents($this->directory . '/config/ssh/relative.conf', "Host relative\n  HostName relative.example.com\n");
        file_put_contents($this->directory . '/home/.ssh/home.conf', "Host home\n  ProxyCommand stays-in-local-files\n");
        $this->oldHome = (string)getenv('HOME');
        putenv('HOME=' . $this->directory . '/home');
    }

    protected function tearDown(): void
    {
        putenv('HOME=' . $this->oldHome);
        exec(sprintf('rm -rf %s', escapeshellarg($this->directory)));
    }

    public function testLoadLocalFiles(): void
    {
        $config = $this->createConfig();
        $relative = $config->addSshConfigFile('ssh/relative.conf');
        $home = $config->addSshConfigFile('~/.ssh/home.conf');
        $absolute = $config->addSshConfigFile($this->directory . '/config/ssh/relative.conf');

        $loader = new SshConfigFileLoader($this->directory . '/cache', $this->createDownloader([]));
        $loader->loadAll($config, new BufferedOutput());

        $this->assertSame($this->directory . '/config/ssh/relative.conf', $relative->getPath());
        $this->assertSame(['relative'], $relative->getHosts());
        $this->assertSame($this->directory . '/home/.ssh/home.conf', $home->getPath());
        $this->assertSame(['home'], $home->getHosts());
        $this->assertSame($this->directory . '/config/ssh/relative.conf', $absolute->getPath());
        $this->assertTrue($config->getSshConnections()->isExternalHost('relative'));
        $this->assertTrue($config->getSshConnections()->isExternalHost('home'));
        $this->assertFalse($config->getSshConnections()->isExternalHost('unknown'));
        $this->assertStringContainsString('ProxyCommand', (string)file_get_contents($this->directory . '/home/.ssh/home.conf'), 'local files are not modified');
    }

    public function testMissingLocalFileIsAnError(): void
    {
        $config = $this->createConfig();
        $config->addSshConfigFile('ssh/missing.conf');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(sprintf('ssh config file ssh/missing.conf (%s/config/ssh/missing.conf) does not exist or is not readable', $this->directory));
        (new SshConfigFileLoader($this->directory . '/cache', $this->createDownloader([])))->loadAll($config, new BufferedOutput());
    }

    public function testDownloadIsSanitizedAndCached(): void
    {
        $config = $this->createConfig();
        $sshConfigFile = $config->addSshConfigFile('https://example.com/configs/ssh_config');
        $downloader = $this->createDownloader(["Host remote\n  HostName remote.example.com\n  ProxyCommand evil\n"]);
        $output = new BufferedOutput(OutputInterface::VERBOSITY_VERBOSE);

        (new SshConfigFileLoader($this->directory . '/cache', $downloader))->loadAll($config, $output);

        $cacheFile = $this->directory . '/cache/example.com-configs-ssh-config-' . substr(sha1('https://example.com/configs/ssh_config'), 0, 8) . '.conf';
        $this->assertSame($cacheFile, $sshConfigFile->getPath());
        $this->assertSame(['remote'], $sshConfigFile->getHosts());
        $this->assertTrue($config->getSshConnections()->isExternalHost('remote'));
        $this->assertSame([['https://example.com/configs/ssh_config', null, null]], $downloader->calls);
        $content = (string)file_get_contents($cacheFile);
        $this->assertStringContainsString("Host remote\n  HostName remote.example.com\n", $content);
        $this->assertStringContainsString('# removed by phpsu: ProxyCommand evil', $content);
        $this->assertStringNotContainsString("\n  ProxyCommand", $content);
        $this->assertSame(0600, fileperms($cacheFile) & 0777);
        $this->assertStringContainsString('removed from ssh config https://example.com/configs/ssh_config: ProxyCommand evil', $output->fetch());
    }

    public function testCacheIsUsedWithinTtl(): void
    {
        $downloader = $this->createDownloader(["Host first\n", "Host second\n"]);
        $loader = new SshConfigFileLoader($this->directory . '/cache', $downloader);

        $config = $this->createConfig();
        $config->addSshConfigFile('https://example.com/ssh_config');

        $loader->loadAll($config, new BufferedOutput());

        $config = $this->createConfig();
        $sshConfigFile = $config->addSshConfigFile('https://example.com/ssh_config');
        $loader->loadAll($config, new BufferedOutput());

        $this->assertCount(1, $downloader->calls);
        $this->assertSame(['first'], $sshConfigFile->getHosts());
    }

    public function testCacheIsRefreshedAfterTtl(): void
    {
        $downloader = $this->createDownloader(["Host first\n", "Host second\n"]);
        $loader = new SshConfigFileLoader($this->directory . '/cache', $downloader);

        $config = $this->createConfig();
        $first = $config->addSshConfigFile('https://example.com/ssh_config', 60);
        $loader->loadAll($config, new BufferedOutput());
        touch((string)$first->getPath(), time() - 61);

        $config = $this->createConfig();
        $sshConfigFile = $config->addSshConfigFile('https://example.com/ssh_config', 60);
        $loader->loadAll($config, new BufferedOutput());

        $this->assertCount(2, $downloader->calls);
        $this->assertSame(['second'], $sshConfigFile->getHosts());
    }

    public function testForceRefresh(): void
    {
        $downloader = $this->createDownloader(["Host first\n", "Host second\n"]);
        $loader = new SshConfigFileLoader($this->directory . '/cache', $downloader);

        $config = $this->createConfig();
        $config->addSshConfigFile('https://example.com/ssh_config');

        $loader->loadAll($config, new BufferedOutput());

        $config = $this->createConfig();
        $sshConfigFile = $config->addSshConfigFile('https://example.com/ssh_config');
        $loader->loadAll($config, new BufferedOutput(), true);

        $this->assertCount(2, $downloader->calls);
        $this->assertSame(['second'], $sshConfigFile->getHosts());
    }

    public function testFailedDownloadUsesStaleCache(): void
    {
        $downloader = $this->createDownloader(["Host first\n", new RuntimeException('download of https://example.com/ssh_config failed with HTTP status 500')]);
        $loader = new SshConfigFileLoader($this->directory . '/cache', $downloader);

        $config = $this->createConfig();
        $first = $config->addSshConfigFile('https://example.com/ssh_config', 0);
        $loader->loadAll($config, new BufferedOutput());

        $config = $this->createConfig();
        $sshConfigFile = $config->addSshConfigFile('https://example.com/ssh_config', 0);
        $output = new BufferedOutput();
        $loader->loadAll($config, $output);

        $this->assertSame($first->getPath(), $sshConfigFile->getPath());
        $this->assertSame(['first'], $sshConfigFile->getHosts());
        $this->assertStringContainsString('Warning: download of https://example.com/ssh_config failed with HTTP status 500, using cached version from ', $output->fetch());
    }

    public function testFailedDownloadWithoutCacheIsOnlyAWarning(): void
    {
        $downloader = $this->createDownloader([new RuntimeException('download of https://example.com/ssh_config failed: timeout')]);
        $config = $this->createConfig();
        $sshConfigFile = $config->addSshConfigFile('https://example.com/ssh_config');
        $output = new BufferedOutput();

        (new SshConfigFileLoader($this->directory . '/cache', $downloader))->loadAll($config, $output);

        $this->assertTrue($sshConfigFile->isLoaded());
        $this->assertNull($sshConfigFile->getPath());
        $this->assertSame([], $sshConfigFile->getHosts());
        $this->assertSame("Warning: download of https://example.com/ssh_config failed: timeout, the hosts of this ssh config file are not available\n", $output->fetch());
    }

    public function testBasicAuthFromUrl(): void
    {
        $downloader = $this->createDownloader(["Host remote\n"]);
        $config = $this->createConfig();
        $sshConfigFile = $config->addSshConfigFile('https://us%40er:p%3Ass@example.com/ssh_config');

        (new SshConfigFileLoader($this->directory . '/cache', $downloader))->loadAll($config, new BufferedOutput());

        $this->assertSame([['https://example.com/ssh_config', 'us@er', 'p:ss']], $downloader->calls);
        $this->assertStringNotContainsString('us', basename((string)$sshConfigFile->getPath(), '.conf'));
        $this->assertStringNotContainsString('p:ss', (string)file_get_contents((string)$sshConfigFile->getPath()));
    }

    public function testExplicitBasicAuthWins(): void
    {
        $downloader = $this->createDownloader(["Host remote\n"]);
        $config = $this->createConfig();
        $config->addSshConfigFile('https://url:credentials@example.com/ssh_config')->setBasicAuth('user', 'secret');

        (new SshConfigFileLoader($this->directory . '/cache', $downloader))->loadAll($config, new BufferedOutput());

        $this->assertSame([['https://example.com/ssh_config', 'user', 'secret']], $downloader->calls);
    }

    public function testAlreadyLoadedFilesAreSkipped(): void
    {
        $downloader = $this->createDownloader(["Host remote\n"]);
        $loader = new SshConfigFileLoader($this->directory . '/cache', $downloader);
        $config = $this->createConfig();
        $config->addSshConfigFile('https://example.com/ssh_config', 0);

        $loader->loadAll($config, new BufferedOutput());
        $loader->loadAll($config, new BufferedOutput(), true);

        $this->assertCount(1, $downloader->calls);
    }

    public function testGetCacheFileName(): void
    {
        $loader = new SshConfigFileLoader($this->directory . '/cache', $this->createDownloader([]));
        $hash = static fn(string $url): string => substr(sha1($url), 0, 8);
        $this->assertSame('example.com-' . $hash('https://example.com') . '.conf', $loader->getCacheFileName('https://example.com'));
        $this->assertSame('git.example.com-raw-main-ssh-config-' . $hash('https://git.example.com/raw/main/SSH_config?x=1') . '.conf', $loader->getCacheFileName('https://git.example.com/raw/main/SSH_config?x=1'));
        $longName = $loader->getCacheFileName('https://example.com/' . str_repeat('a', 100));
        $this->assertSame(60 + 1 + 8 + 5, strlen($longName));
    }

    public function testDefaultCacheDirectory(): void
    {
        $this->assertSame(getcwd() . '/.phpsu/ssh-configs', (new SshConfigFileLoader())->getCacheDirectory());
    }

    public function testWarningsAreWrittenToStderr(): void
    {
        $downloader = $this->createDownloader([new RuntimeException('download failed')]);
        $config = $this->createConfig();
        $config->addSshConfigFile('https://example.com/ssh_config');

        $output = new BufferedConsoleOutput();

        (new SshConfigFileLoader($this->directory . '/cache', $downloader))->loadAll($config, $output);

        $stderr = $output->getErrorOutput();
        $this->assertInstanceOf(StreamOutput::class, $stderr);
        rewind($stderr->getStream());
        $this->assertSame("Warning: download failed, the hosts of this ssh config file are not available\n", stream_get_contents($stderr->getStream()));
        rewind($output->getStream());
        $this->assertSame('', stream_get_contents($output->getStream()));
    }

    public function testOnlyNotLoadedFilesAreLoaded(): void
    {
        $config = $this->createConfig();
        $loaded = $config->addSshConfigFile('ssh/missing.conf')->setLoaded('/somewhere', ['loaded']);
        $notLoaded = $config->addSshConfigFile('ssh/relative.conf');

        (new SshConfigFileLoader($this->directory . '/cache', $this->createDownloader([])))->loadAll($config, new BufferedOutput());

        $this->assertSame('/somewhere', $loaded->getPath());
        $this->assertSame($this->directory . '/config/ssh/relative.conf', $notLoaded->getPath());
        $this->assertFalse($config->getSshConnections()->isExternalHost('loaded'));
        $this->assertTrue($config->getSshConnections()->isExternalHost('relative'));
    }

    public function testTrailingSlashes(): void
    {
        putenv('HOME=' . $this->directory . '/home/');
        $config = (new GlobalConfig())->setConfigDirectory($this->directory . '/config/');
        $relative = $config->addSshConfigFile('ssh/relative.conf');
        $home = $config->addSshConfigFile('~/.ssh/home.conf');

        (new SshConfigFileLoader($this->directory . '/cache', $this->createDownloader([])))->loadAll($config, new BufferedOutput());

        $this->assertSame($this->directory . '/config/ssh/relative.conf', $relative->getPath());
        $this->assertSame($this->directory . '/home/.ssh/home.conf', $home->getPath());
    }

    public function testTildeOnlyIsHome(): void
    {
        $config = $this->createConfig();
        $config->addSshConfigFile('~');

        $this->expectExceptionMessage(sprintf('ssh config file ~ (%s/home) does not exist or is not readable', $this->directory));
        (new SshConfigFileLoader($this->directory . '/cache', $this->createDownloader([])))->loadAll($config, new BufferedOutput());
    }

    public function testTildeWithoutHome(): void
    {
        putenv('HOME=');
        $config = $this->createConfig();
        $config->addSshConfigFile('~/.ssh/home.conf');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('could not resolve ~/.ssh/home.conf: HOME is not set');
        (new SshConfigFileLoader($this->directory . '/cache', $this->createDownloader([])))->loadAll($config, new BufferedOutput());
    }

    public function testTildeUserIsRelative(): void
    {
        $config = $this->createConfig();
        $config->addSshConfigFile('~user/config');

        $this->expectExceptionMessage(sprintf('ssh config file ~user/config (%s/config/~user/config) does not exist or is not readable', $this->directory));
        (new SshConfigFileLoader($this->directory . '/cache', $this->createDownloader([])))->loadAll($config, new BufferedOutput());
    }

    public function testCacheFileContent(): void
    {
        $config = $this->createConfig();
        $sshConfigFile = $config->addSshConfigFile('https://example.com/ssh_config');
        $downloader = $this->createDownloader(["Host remote\n  LocalCommand evil\n  User remote"]);

        (new SshConfigFileLoader($this->directory . '/cache/sub', $downloader))->loadAll($config, new BufferedOutput());

        $this->assertMatchesRegularExpression(
            '/^# downloaded by phpsu from https:\/\/example\.com\/ssh_config at \d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}' . "\n"
            . '# removed by phpsu: LocalCommand evil' . "\n\n"
            . 'Host remote' . "\n" . '  User remote' . "\n" . '$/D',
            (string)file_get_contents((string)$sshConfigFile->getPath())
        );
        $this->assertSame([], glob($this->directory . '/cache/sub/*.tmp'));
    }

    public function testCacheDirectoryCanNotBeCreated(): void
    {
        file_put_contents($this->directory . '/file', '');
        $config = $this->createConfig();
        $config->addSshConfigFile('https://example.com/ssh_config');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(sprintf('Directory "%s/file/cache" was not created', $this->directory));
        (new SshConfigFileLoader($this->directory . '/file/cache', $this->createDownloader(["Host remote\n"])))->loadAll($config, new BufferedOutput());
    }

    public function testCacheFileCanNotBeWritten(): void
    {
        $loader = new SshConfigFileLoader($this->directory . '/cache', $this->createDownloader(["Host remote\n"]));
        $cacheFile = $this->directory . '/cache/' . $loader->getCacheFileName('https://example.com/ssh_config');
        mkdir($cacheFile . '.' . getmypid() . '.tmp', 0777, true);
        $config = $this->createConfig();
        $config->addSshConfigFile('https://example.com/ssh_config');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('could not write ssh config cache file ' . $cacheFile);
        $loader->loadAll($config, new BufferedOutput());
    }

    public function testStaleCacheWarningContainsDate(): void
    {
        $downloader = $this->createDownloader(["Host first\n", new RuntimeException('failed')]);
        $loader = new SshConfigFileLoader($this->directory . '/cache', $downloader);
        $config = $this->createConfig();
        $first = $config->addSshConfigFile('https://example.com/ssh_config');
        $loader->loadAll($config, new BufferedOutput());

        $time = (int)mktime(12, 30, 15, 6, 1, 2024);
        touch((string)$first->getPath(), $time);

        $config = $this->createConfig();
        $config->addSshConfigFile('https://example.com/ssh_config');

        $output = new BufferedOutput();
        $loader->loadAll($config, $output);
        $this->assertSame("Warning: failed, using cached version from 2024-06-01 12:30:15\n", $output->fetch());
    }

    private function createConfig(): GlobalConfig
    {
        return (new GlobalConfig())->setConfigDirectory($this->directory . '/config');
    }

    /**
     * @param array<string|RuntimeException> $responses
     */
    private function createDownloader(array $responses): FakeSshConfigDownloader
    {
        return new FakeSshConfigDownloader($responses);
    }
}
