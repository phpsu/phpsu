<?php

declare(strict_types=1);

namespace PHPSu\Helper;

use Exception;
use Symfony\Component\Console\Output\OutputInterface;

use function strlen;

/**
 * @internal
 */
final class StringHelper
{
    /**
     * @return string[]
     */
    public static function splitString(string $string, int $maxLength): array
    {
        $exploded = explode(' ', $string);
        $full = array_shift($exploded);
        while (count($exploded)) {
            $part = array_shift($exploded);
            if (strlen($full . ' ' . $part) > $maxLength) {
                array_unshift($exploded, $part);
                $result = [$full];
                array_push($result, ...self::splitString(implode(' ', $exploded), $maxLength));
                return $result;
            }

            $full .= ' ' . $part;
        }

        return [$full];
    }

    /**
     * @param string[] $haystack
     */
    public static function findStringInArray(string $needle, array $haystack): ?string
    {
        $remaining = array_filter($haystack, static fn (string $el): bool => stripos($el, $needle) === 0);
        if (count($remaining) === 1) {
            return array_shift($remaining);
        }

        return '';
    }

    public static function optionStringForVerbosity(int $verbosity): string
    {
        $verbosities = [
            OutputInterface::VERBOSITY_QUIET => 'q',
            OutputInterface::VERBOSITY_NORMAL => '',
            OutputInterface::VERBOSITY_VERBOSE => 'v',
            OutputInterface::VERBOSITY_VERY_VERBOSE => 'vv',
            OutputInterface::VERBOSITY_DEBUG => 'vvv',
        ];
        if (isset($verbosities[$verbosity])) {
            return $verbosities[$verbosity];
        }

        throw new Exception(sprintf('Verbosity %d is not defined', $verbosity));
    }
}
