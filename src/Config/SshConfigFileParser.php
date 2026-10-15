<?php

declare(strict_types=1);

namespace PHPSu\Config;

/**
 * @internal
 */
final class SshConfigFileParser
{
    /**
     * the only keywords kept in downloaded ssh config files (lowercase => written as)
     * everything that executes commands, loads code, includes files, forwards ports or sends local data is missing on purpose
     * @var array<string, string>
     */
    private const ALLOWED_KEYWORDS = [
        'host' => 'Host',
        'match' => 'Match',
        'hostname' => 'HostName',
        'user' => 'User',
        'port' => 'Port',
        'proxyjump' => 'ProxyJump',
        'identityfile' => 'IdentityFile',
        'identitiesonly' => 'IdentitiesOnly',
        'identityagent' => 'IdentityAgent',
        'certificatefile' => 'CertificateFile',
        'addkeystoagent' => 'AddKeysToAgent',
        'forwardagent' => 'ForwardAgent',
        'preferredauthentications' => 'PreferredAuthentications',
        'pubkeyauthentication' => 'PubkeyAuthentication',
        'passwordauthentication' => 'PasswordAuthentication',
        'kbdinteractiveauthentication' => 'KbdInteractiveAuthentication',
        'pubkeyacceptedalgorithms' => 'PubkeyAcceptedAlgorithms',
        'pubkeyacceptedkeytypes' => 'PubkeyAcceptedKeyTypes',
        'hostkeyalgorithms' => 'HostKeyAlgorithms',
        'hostkeyalias' => 'HostKeyAlias',
        'kexalgorithms' => 'KexAlgorithms',
        'ciphers' => 'Ciphers',
        'macs' => 'MACs',
        'stricthostkeychecking' => 'StrictHostKeyChecking',
        'userknownhostsfile' => 'UserKnownHostsFile',
        'globalknownhostsfile' => 'GlobalKnownHostsFile',
        'checkhostip' => 'CheckHostIP',
        'hashknownhosts' => 'HashKnownHosts',
        'updatehostkeys' => 'UpdateHostKeys',
        'verifyhostkeydns' => 'VerifyHostKeyDNS',
        'connecttimeout' => 'ConnectTimeout',
        'connectionattempts' => 'ConnectionAttempts',
        'serveraliveinterval' => 'ServerAliveInterval',
        'serveralivecountmax' => 'ServerAliveCountMax',
        'tcpkeepalive' => 'TCPKeepAlive',
        'compression' => 'Compression',
        'addressfamily' => 'AddressFamily',
        'batchmode' => 'BatchMode',
        'loglevel' => 'LogLevel',
        'controlmaster' => 'ControlMaster',
        'controlpath' => 'ControlPath',
        'controlpersist' => 'ControlPersist',
        'canonicalizehostname' => 'CanonicalizeHostname',
        'canonicaldomains' => 'CanonicalDomains',
        'canonicalizefallbacklocal' => 'CanonicalizeFallbackLocal',
        'canonicalizemaxdots' => 'CanonicalizeMaxDots',
        'canonicalizepermittedcnames' => 'CanonicalizePermittedCNAMEs',
    ];

    /**
     * values of these keywords end up in the shell command ssh creates for ProxyJump, so only plain values are allowed
     * @var array<string, string>
     */
    private const VALIDATED_KEYWORDS = [
        'hostname' => '/^[A-Za-z0-9._:%-]+$/',
        'port' => '/^\d{1,5}$/',
        'proxyjump' => '/^(?:none|(?:ssh:\/\/)?(?:[A-Za-z0-9._-]+@)?(?:[A-Za-z0-9._-]+|\[[0-9A-Fa-f:.]+\])(?::\d{1,5})?(?:,(?:ssh:\/\/)?(?:[A-Za-z0-9._-]+@)?(?:[A-Za-z0-9._-]+|\[[0-9A-Fa-f:.]+\])(?::\d{1,5})?)*)$/i',
    ];

    /**
     * Match criteria => whether they take an argument (exec and everything unknown is not allowed)
     * @var array<string, bool>
     */
    private const ALLOWED_MATCH_CRITERIA = [
        'all' => false,
        'canonical' => false,
        'final' => false,
        'host' => true,
        'originalhost' => true,
        'user' => true,
        'localuser' => true,
        'localnetwork' => true,
        'tagged' => true,
    ];

    /**
     * Only keeps allowed keywords with valid values and writes them again from the parsed tokens,
     * so ssh reads exactly what was validated (no matter how the original line was quoted or escaped).
     * Match blocks with exec or unknown criteria are removed completely, otherwise their options would apply to all hosts.
     *
     * @return array{content: string, removed: string[]}
     */
    public function sanitize(string $content): array
    {
        $result = [];
        $removed = [];
        $insideRemovedMatchBlock = false;
        foreach (preg_split('/\R/', $content) ?: [] as $line) {
            $tokens = $this->tokenize($line);
            if ($tokens === []) {
                $result[] = '';
                continue;
            }

            $keyword = strtolower($tokens[0] ?? '');
            $arguments = array_slice($tokens ?? [], 1);
            if ($keyword === 'host' || $keyword === 'match') {
                $insideRemovedMatchBlock = $keyword === 'match' && !$this->isValidMatch($arguments);
            }

            if ($tokens === null || $insideRemovedMatchBlock || !$this->isValid($keyword, $arguments)) {
                $removed[] = trim($line);
                continue;
            }

            $indent = $keyword === 'host' || $keyword === 'match' ? '' : '  ';
            $result[] = $indent . self::ALLOWED_KEYWORDS[$keyword] . ' ' . implode(' ', array_map($this->quote(...), $arguments));
        }

        return ['content' => implode(PHP_EOL, $result), 'removed' => $removed];
    }

    /**
     * returns all concrete host names (patterns with wildcards or negations are ignored)
     *
     * @return string[]
     */
    public function extractHosts(string $content): array
    {
        $hosts = [];
        foreach (preg_split('/\R/', $content) ?: [] as $line) {
            $tokens = $this->tokenize($line) ?? [];
            if (strtolower($tokens[0] ?? '') !== 'host') {
                continue;
            }

            foreach (array_slice($tokens, 1) as $pattern) {
                if ($pattern === '' || strpbrk($pattern, '*?!') !== false) {
                    continue;
                }

                $hosts[$pattern] = $pattern;
            }
        }

        return array_values($hosts);
    }

    /**
     * splits a line like OpenSSH does: the keyword may be double quoted and ends at whitespace or a single "=",
     * the arguments support single and double quotes, backslash escapes and # comments.
     *
     * @return null|string[] null if the quotes are invalid, an empty array for empty lines and comments
     */
    public function tokenize(string $line): ?array
    {
        $length = strlen($line);
        $position = strspn($line, " \t\f\r\n");
        if ($position >= $length || $line[$position] === '#') {
            return [];
        }

        if ($line[$position] === '"') {
            $end = strpos($line, '"', $position + 1);
            if ($end === false) {
                return null;
            }

            $keyword = substr($line, $position + 1, $end - $position - 1);
            $position = $end + 1;
        } else {
            $keywordLength = strcspn($line, " \t\f\r\n=", $position);
            $keyword = substr($line, $position, $keywordLength);
            $position += $keywordLength;
        }

        $position += strspn($line, " \t\f\r\n", $position);
        if ($position < $length && $line[$position] === '=') {
            $position++;
        }

        $tokens = [$keyword];
        while (true) {
            $position += strspn($line, " \t\f\r\n", $position);
            if ($position >= $length || $line[$position] === '#') {
                break;
            }

            $argument = '';
            $quote = null;
            for (; $position < $length; $position++) {
                $char = $line[$position];
                $next = $line[$position + 1] ?? '';
                if ($char === '\\' && (in_array($next, ["'", '"', '\\'], true) || ($quote === null && $next === ' '))) {
                    $argument .= $next;
                    $position++;
                } elseif ($quote === null && str_contains(" \t\f\r\n", $char)) {
                    break;
                } elseif ($quote === null && ($char === '"' || $char === "'")) {
                    $quote = $char;
                } elseif ($quote === $char) {
                    $quote = null;
                } else {
                    $argument .= $char;
                }
            }

            if ($quote !== null) {
                return null;
            }

            $tokens[] = $argument;
        }

        return $keyword === '' ? null : $tokens;
    }

    /**
     * @param string[] $arguments
     */
    private function isValid(string $keyword, array $arguments): bool
    {
        if (!isset(self::ALLOWED_KEYWORDS[$keyword]) || $arguments === []) {
            return false;
        }

        foreach ($arguments as $argument) {
            // quotes, backslashes and control characters can not be written back safely
            if (preg_match('/["\\\\\x00-\x1f\x7f]/', $argument)) {
                return false;
            }
        }

        if (isset(self::VALIDATED_KEYWORDS[$keyword])) {
            return count($arguments) === 1 && preg_match(self::VALIDATED_KEYWORDS[$keyword], $arguments[0]);
        }

        return true;
    }

    /**
     * @param string[] $arguments
     */
    private function isValidMatch(array $arguments): bool
    {
        if ($arguments === []) {
            return false;
        }

        while ($arguments !== []) {
            $criteria = strtolower(ltrim(array_shift($arguments), '!'));
            if (!isset(self::ALLOWED_MATCH_CRITERIA[$criteria])) {
                return false;
            }

            if (self::ALLOWED_MATCH_CRITERIA[$criteria] && array_shift($arguments) === null) {
                return false;
            }
        }

        return true;
    }

    private function quote(string $argument): string
    {
        if (preg_match('/^[A-Za-z0-9._%:@\/+,~*?!\[\]-]+$/', $argument)) {
            return $argument;
        }

        return '"' . $argument . '"';
    }
}
