<?php

declare(strict_types=1);

namespace PHPSu\Process;

/**
 * @internal
 */
final class Spinner
{
    public const PONG = [
        '(      )',
        '(●     )',
        '( ●    )',
        '(  ●   )',
        '(   ●  )',
        '(    ● )',
        '(     ●)',
        '(      )',
        '(      )',
    ];

    private int $state = 0;

    public function spin(): string
    {
        if ($this->state >= count(self::PONG)) {
            $this->state = 0;
        }
        return self::PONG[$this->state++];
    }
}
