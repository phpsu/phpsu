<?php

declare(strict_types=1);

namespace Phpsu\Phpsu;

use Exception;
use Throwable;

use const PHP_EOL;

final class MultiThrowableException extends Exception
{
    /**
     * @var list<Throwable>
     */
    public readonly array $throwables;

    public function __construct(Throwable ...$throwables)
    {
        $this->throwables = $throwables;

        $messageArray = array_map(fn(Throwable $throwable): string => '  - ' . $throwable->getMessage(), $throwables);
        $messages = implode(PHP_EOL, $messageArray);
        parent::__construct('Multiple exceptions occurred:' . PHP_EOL . $messages);
    }

    public static function throw(Throwable ...$throwable): void
    {
        if (!$throwable) {
            return;
        }

        if (count($throwable) === 1) {
            throw $throwable[0];
        }

        throw new MultiThrowableException(...$throwable);
    }
}
