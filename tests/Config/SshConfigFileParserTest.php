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

    public function testSanitizeAllowsPlainHostNameAndProxyJump(): void
    {
        $content = implode("\n", [
            'Host hosta',
            '  HostName a.example.com',
            '  HostName=%h.example.com',
            '  HostName "2001:db8::1"',
            '  HostName b.example.com   ',
            '  ProxyJump bastion',
            '  ProxyJump user@bastion:2222,ssh://jump-user@[2001:db8::1]:22',
            '  proxyjump=NONE',
        ]);
        $result = (new SshConfigFileParser())->sanitize($content);
        $this->assertSame(str_replace("\n", PHP_EOL, $content), $result['content']);
        $this->assertSame([], $result['removed']);
    }

    /**
     * OpenSSH puts the ProxyJump user and the HostName (%h) into the shell command it creates for ProxyJump
     */
    public function testSanitizeRemovesShellMetacharactersFromHostNameAndProxyJump(): void
    {
        $content = implode("\n", [
            'Host hosta',
            '  ProxyJump u$(touch /tmp/pwned)@bastion',
            '  ProxyJump ssh://u`id`@bastion',
            '  ProxyJump bastion;id',
            '  ProxyJump bastion|id',
            '  ProxyJump bastion,u$(id)@other',
            '  ProxyJump bastion:22$(id)',
            '  ProxyJump "u\'id\'@bastion"',
            '  ProxyJump',
            '  HostName "x\']$(touch /tmp/pwned)\'"',
            '  HostName a.example.com;id',
            '  HostName a b',
            '  HostName "a.example.com"$(id)',
            '  User u',
        ]);
        $result = (new SshConfigFileParser())->sanitize($content);
        $this->assertSame(implode(PHP_EOL, ['Host hosta', '  User u']), $result['content']);
        $this->assertCount(12, $result['removed']);
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
