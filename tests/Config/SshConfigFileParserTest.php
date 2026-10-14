<?php

declare(strict_types=1);

namespace PHPSu\Tests\Config;

use PHPSu\Config\SshConfigFileParser;
use PHPUnit\Framework\TestCase;

final class SshConfigFileParserTest extends TestCase
{
    public function testSanitizeRemovesCommandKeywords(): void
    {
        $content = implode("\n", [
            '# comment ProxyCommand stays',
            'Host hosta',
            '  HostName a.example.com',
            '  ProxyCommand ssh -W %h:%p bastion',
            '  proxycommand=nc %h %p',
            '  LocalCommand touch /tmp/pwned',
            '  PermitLocalCommand yes',
            '  RemoteCommand bash',
            '  KnownHostsCommand /bin/false',
            '  PKCS11Provider /tmp/evil.so',
            '  SecurityKeyProvider /tmp/evil.so',
            '  XAuthLocation /tmp/evil',
            '  ProxyJump bastion',
            'Include ~/.ssh/other',
            'Host hostb',
            '  User b',
        ]);
        $result = (new SshConfigFileParser())->sanitize($content);
        $this->assertSame(implode(PHP_EOL, [
            '# comment ProxyCommand stays',
            'Host hosta',
            '  HostName a.example.com',
            '  ProxyJump bastion',
            'Host hostb',
            '  User b',
        ]), $result['content']);
        $this->assertSame([
            'ProxyCommand ssh -W %h:%p bastion',
            'proxycommand=nc %h %p',
            'LocalCommand touch /tmp/pwned',
            'PermitLocalCommand yes',
            'RemoteCommand bash',
            'KnownHostsCommand /bin/false',
            'PKCS11Provider /tmp/evil.so',
            'SecurityKeyProvider /tmp/evil.so',
            'XAuthLocation /tmp/evil',
            'Include ~/.ssh/other',
        ], $result['removed']);
    }

    public function testSanitizeRemovesCompleteMatchExecBlocks(): void
    {
        $content = implode("\n", [
            'Match host hosta exec "test -f /tmp/x"',
            '  User root',
            'Match host hostb !exec="true"',
            '  User root',
            'Match all',
            '  User everyone',
            'Host hostc',
            '  User c',
            'Match EXEC true',
            '  User root',
            'Host hostd',
            '  User d',
            'Match host executor',
            '  User e',
        ]);
        $result = (new SshConfigFileParser())->sanitize($content);
        $this->assertSame(implode(PHP_EOL, [
            'Match all',
            '  User everyone',
            'Host hostc',
            '  User c',
            'Host hostd',
            '  User d',
            'Match host executor',
            '  User e',
        ]), $result['content']);
        $this->assertCount(6, $result['removed']);
    }

    public function testExtractHosts(): void
    {
        $content = implode("\n", [
            '# Host commented',
            'Host hosta hostb',
            '  HostName a.example.com',
            'host=hostc',
            'Host *.example.com !hostd host?',
            'Host "" "quoted host" hosta',
            'Host *',
            'Match host matched',
            '  HostName hostname.example.com',
        ]);
        $this->assertSame(['hosta', 'hostb', 'hostc', 'quoted host'], (new SshConfigFileParser())->extractHosts($content));
    }
}
