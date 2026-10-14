<?php

declare(strict_types=1);

namespace PHPSu\Config;

/**
 * @internal
 */
final class SshConfigFileParser
{
    /**
     * keywords that execute local commands, load code or include further files
     * @var string[]
     */
    private const FORBIDDEN_KEYWORDS = [
        'include',
        'knownhostscommand',
        'localcommand',
        'permitlocalcommand',
        'pkcs11provider',
        'proxycommand',
        'remotecommand',
        'securitykeyprovider',
        'xauthlocation',
    ];

    /**
     * removes all keywords that execute commands.
     * Match blocks containing an exec criteria are removed completely, otherwise their options would apply to all hosts.
     *
     * @return array{content: string, removed: string[]}
     */
    public function sanitize(string $content): array
    {
        $result = [];
        $removed = [];
        $insideRemovedMatchBlock = false;
        foreach (preg_split('/\R/', $content) ?: [] as $line) {
            $keyword = $this->getKeyword($line);
            if ($keyword === 'host' || $keyword === 'match') {
                $insideRemovedMatchBlock = $keyword === 'match' && preg_match('/(^|[\s=])!?exec([\s=]|$)/i', $this->getArguments($line));
            }

            if ($insideRemovedMatchBlock || in_array($keyword, self::FORBIDDEN_KEYWORDS, true)) {
                $removed[] = trim($line);
                continue;
            }

            $result[] = $line;
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
            if ($this->getKeyword($line) !== 'host') {
                continue;
            }

            preg_match_all('/"([^"]*)"|(\S+)/', $this->getArguments($line), $matches, PREG_SET_ORDER);
            foreach ($matches as $match) {
                $pattern = $match[2] ?? $match[1] ?? '';
                if ($pattern === '' || strpbrk($pattern, '*?!') !== false) {
                    continue;
                }

                $hosts[$pattern] = $pattern;
            }
        }

        return array_values($hosts);
    }

    private function getKeyword(string $line): string
    {
        if (!preg_match('/^\s*([A-Za-z0-9]+)/', $line, $matches)) {
            return '';
        }

        return strtolower($matches[1]);
    }

    private function getArguments(string $line): string
    {
        return (string)preg_replace('/^\s*[A-Za-z0-9]+\s*=?\s*/', '', $line, 1);
    }
}
