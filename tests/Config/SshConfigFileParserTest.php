<?php

declare(strict_types=1);

namespace PHPSu\Tests\Config;

use PHPSu\Config\SshConfigFileParser;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class SshConfigFileParserTest extends TestCase
{
    public function testSanitizeRemovesCommandKeywords(): void
    {
        $content = implode("\n", [
            '# comment ProxyCommand stays inert',
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
            '  LocalForward 8080 localhost:80',
            '  SendEnv *',
            '  UnknownKeyword value',
            '  ProxyJump bastion',
            'Include ~/.ssh/other',
            'Host hostb',
            '  User b',
        ]);
        $result = (new SshConfigFileParser())->sanitize($content);
        $this->assertSame(implode(PHP_EOL, [
            '',
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
            'LocalForward 8080 localhost:80',
            'SendEnv *',
            'UnknownKeyword value',
            'Include ~/.ssh/other',
        ], $result['removed']);
    }

    /**
     * OpenSSH removes quotes from keywords and Match criteria, so they must not bypass the allowlist
     */
    public function testSanitizeHandlesQuotedKeywordsLikeOpenSsh(): void
    {
        $content = implode("\n", [
            'Host target',
            '  "ProxyCommand" touch /tmp/pwned',
            '  "ProxyCommand"=touch /tmp/pwned',
            '  "LocalCommand" touch /tmp/pwned',
            '  "HostName" "x\']$(touch /tmp/pwned)\'"',
            '  "HostName" a.example.com',
            '  "User"=deploy',
            'Match "exec" "touch /tmp/pwned"',
            '  User test',
            'Match host target !"EXEC" "touch /tmp/pwned"',
            '  User test',
            'Match "host" target "user" deploy',
            '  User matched',
        ]);
        $result = (new SshConfigFileParser())->sanitize($content);
        $this->assertSame(implode(PHP_EOL, [
            'Host target',
            '  HostName a.example.com',
            '  User deploy',
            'Match host target user deploy',
            '  User matched',
        ]), $result['content']);
        $this->assertCount(8, $result['removed']);
    }

    public function testSanitizeRemovesInvalidMatchBlocks(): void
    {
        $content = implode("\n", [
            'User before-any-host',
            'Match host hosta exec "test -f /tmp/x"',
            '  User root',
            'Match host hostb !exec="true"',
            '  User root',
            'Match version 9',
            '  User root',
            'Match host',
            '  User root',
            'Match',
            '  User root',
            'Match all',
            '  User everyone',
            'Host hostc',
            '  User c',
            'Match EXEC true',
            '  User root',
            'Host hostd',
            '  User d',
            'Match host executor !user root canonical final originalhost o localuser l localnetwork 10.0.0.0/8 tagged t',
            '  User e',
            'Match HOST upper',
            '  User f',
        ]);
        $result = (new SshConfigFileParser())->sanitize($content);
        $this->assertSame(implode(PHP_EOL, [
            '  User before-any-host',
            'Match all',
            '  User everyone',
            'Host hostc',
            '  User c',
            'Host hostd',
            '  User d',
            'Match host executor !user root canonical final originalhost o localuser l localnetwork 10.0.0.0/8 tagged t',
            '  User e',
            'Match HOST upper',
            '  User f',
        ]), $result['content']);
        $this->assertCount(12, $result['removed']);
    }

    public function testSanitizeWritesCanonicalLines(): void
    {
        $content = implode("\n", [
            'host=hosta "host b"',
            "\tHOSTNAME = a.example.com # trailing comment",
            '  UserKnownHostsFile "~/.ssh/known hosts" \'~/.ssh/other\'',
            '  identityfile "~/.ssh/it\'s key"',
            '  ControlPath ~/.ssh/cm-%r@%h:%p',
            '  SetEnv FOO=bar',
            '  CanonicalDomains example.com example.org',
            '  IdentityFile =odd',
            '  UserKnownHostsFile ~/.ssh/a =b',
            '',
        ]);
        $result = (new SshConfigFileParser())->sanitize($content);
        $this->assertSame(implode(PHP_EOL, [
            'Host hosta "host b"',
            '  HostName a.example.com',
            '  UserKnownHostsFile "~/.ssh/known hosts" ~/.ssh/other',
            '  IdentityFile "~/.ssh/it\'s key"',
            '  ControlPath ~/.ssh/cm-%r@%h:%p',
            '  CanonicalDomains example.com example.org',
            '  IdentityFile odd',
            '  UserKnownHostsFile ~/.ssh/a "=b"',
            '',
        ]), $result['content']);
        $this->assertSame(['SetEnv FOO=bar'], $result['removed']);
    }

    public function testSanitizeRemovesValuesThatCanNotBeWrittenBack(): void
    {
        $content = implode("\n", [
            'Host hosta',
            '  User "unterminated',
            '  IdentityFile "a\"b"',
            '  IdentityFile a\\\\b',
            "  IdentityFile \"a\x01b\"",
            '  User',
            '  ""',
        ]);
        $result = (new SshConfigFileParser())->sanitize($content);
        $this->assertSame('Host hosta', $result['content']);
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
            '  Port 2222',
            '  ProxyJump bastion',
            '  ProxyJump user@bastion:2222,ssh://jump-user@bastion2:22,jump-user@[2001:db8::1]:22',
            '  proxyjump=NONE',
        ]);
        $result = (new SshConfigFileParser())->sanitize($content);
        $this->assertSame(implode(PHP_EOL, [
            'Host hosta',
            '  HostName a.example.com',
            '  HostName %h.example.com',
            '  HostName 2001:db8::1',
            '  HostName b.example.com',
            '  Port 2222',
            '  ProxyJump bastion',
            '  ProxyJump user@bastion:2222,ssh://jump-user@bastion2:22,jump-user@[2001:db8::1]:22',
            '  ProxyJump NONE',
        ]), $result['content']);
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
            '  ProxyJump bastion other',
            '  ProxyJump',
            '  HostName "x\']$(touch /tmp/pwned)\'"',
            '  HostName a.example.com;id',
            '  HostName "a b"',
            '  HostName "a.example.com"$(id)',
            '  Port 22$(id)',
            '  User u',
        ]);
        $result = (new SshConfigFileParser())->sanitize($content);
        $this->assertSame(implode(PHP_EOL, ['Host hosta', '  User u']), $result['content']);
        $this->assertCount(14, $result['removed']);
    }

    /**
     * @return array<string, array{string, null|string[]}>
     */
    public static function tokenizeProvider(): array
    {
        return [
            'empty' => ['', []],
            'whitespace' => [" \t ", []],
            'comment' => ['  # Host x', []],
            'simple' => ['Host a b', ['Host', 'a', 'b']],
            'equals' => ['Port=22', ['Port', '22']],
            'equals with spaces' => ['Port = 22', ['Port', '22']],
            'quoted keyword' => ['"ProxyCommand" x', ['ProxyCommand', 'x']],
            'quoted keyword with equals' => ['"ProxyCommand"=x y', ['ProxyCommand', 'x', 'y']],
            'unterminated keyword' => ['"ProxyCommand x', null],
            'empty keyword' => ['"" x', null],
            'empty keyword with quoted argument' => ['"" "x"', null],
            'quoted keyword followed by text' => ['"Proxy"Command x', ['Proxy', 'Command', 'x']],
            'double quotes' => ['User "a b" c', ['User', 'a b', 'c']],
            'single quotes' => ["User 'a \"b' c", ['User', 'a "b', 'c']],
            'mixed quotes' => ['User a"b c"d', ['User', 'ab cd']],
            'escaped space' => ['User a\ b', ['User', 'a b']],
            'escaped quote' => ['User a\"b', ['User', 'a"b']],
            'escaped single quote' => ["User a\\'b", ['User', "a'b"]],
            'escaped backslash' => ['User a\\\\b', ['User', 'a\\b']],
            'other backslash' => ['User a\nb', ['User', 'a\nb']],
            'escaped space in quotes' => ['User "a\ b"', ['User', 'a\ b']],
            'trailing comment' => ['User a # b', ['User', 'a']],
            'hash inside argument' => ['User a#b', ['User', 'a#b']],
            'unterminated argument' => ['User "a', null],
            'empty argument' => ['User ""', ['User', '']],
        ];
    }

    /**
     * @param null|string[] $expected
     */
    #[DataProvider('tokenizeProvider')]
    public function testTokenize(string $line, ?array $expected): void
    {
        $this->assertSame($expected, (new SshConfigFileParser())->tokenize($line));
    }

    public function testExtractHosts(): void
    {
        $content = implode("\n", [
            '# Host commented',
            'Host hosta hostb',
            '  HostName a.example.com',
            'host=hostc',
            '"Host" hostd',
            'Host *.example.com !hoste host?',
            'Host "" "quoted host" hosta',
            'Host "unterminated',
            'Host *',
            'Match host matched',
            '  HostName hostname.example.com',
        ]);
        $this->assertSame(['hosta', 'hostb', 'hostc', 'hostd', 'quoted host'], (new SshConfigFileParser())->extractHosts($content));
    }
}
