<?php

declare(strict_types=1);

namespace PHPSu\Helper;

use Exception;
use Symfony\Component\Console\Output\OutputInterface;

final class StringHelper
{
    /**
     * @param string $string
     * @param int $maxLength
     * @return array<string>
     */
    public static function splitString(string $string, int $maxLength): array
    {
        $exploded = explode(' ', $string);
        /** @var string $full phpstan bug https://github.com/phpstan/phpstan/issues/1723 */
        $full = array_shift($exploded);
        while (count($exploded)) {
            $part = array_shift($exploded);
            if (strlen($full . ' ' . $part) > $maxLength) {
                array_unshift($exploded, $part);
                $result = [$full];
                array_push($result, ...static::splitString(implode(' ', $exploded), $maxLength));
                return $result;
            }
            $full .= ' ' . $part;
        }
        return [$full];
    }

    /**
     * @param string $needle
     * @param array<string> $haystack
     * @return string|null
     */
    public static function findStringInArray(string $needle, array $haystack): ?string
    {
        $remaining = array_filter($haystack, static function ($el) use ($needle) {
            return stripos($el, $needle) === 0;
        });
        if (count($remaining) === 1) {
            return array_shift($remaining);
        }
        return '';
    }

    public static function optionStringForVerbosity(int $verbosity): string
    {
        switch ($verbosity) {
            case OutputInterface::VERBOSITY_QUIET:
                return '-q ';
            case OutputInterface::VERBOSITY_NORMAL:
                return '';
            case OutputInterface::VERBOSITY_VERBOSE:
                return '-v ';
            case OutputInterface::VERBOSITY_VERY_VERBOSE:
                return '-vv ';
            case OutputInterface::VERBOSITY_DEBUG:
                return '-vvv ';
        }
        throw new Exception(sprintf('Verbosity %d is not defined', $verbosity));
    }
}
