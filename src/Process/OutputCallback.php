<?php

declare(strict_types=1);

namespace PHPSu\Process;

use PHPSu\Helper\StringHelper;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @internal
 */
final class OutputCallback
{
    public function __construct(private OutputInterface $output)
    {
    }

    /**
     * @param Process<mixed> $process
     */
    public function __invoke(Process $process, string $type, string $data): void
    {
        $output = $this->output;
        $verbosity = OutputInterface::VERBOSITY_VERBOSE;
        $color = 'yellow';
        if ($type === Process::ERR) {
            $verbosity = OutputInterface::VERBOSITY_QUIET;
            $color = 'red';
            if ($output instanceof ConsoleOutputInterface) {
                $output = $output->getErrorOutput();
            }
        }

        $formatter = $output->getFormatter();
        $prefix = $formatter->format(sprintf('<fg=%s>%s:</> ', $color, $process->getName()));
        $outputString = '';
        $dataLines = explode(PHP_EOL, trim($data));
        foreach ($dataLines as $line) {
            foreach (StringHelper::splitString($line, 80) as $extraLine) {
                $outputString .= $prefix . $extraLine . PHP_EOL;
            }
        }

        $output->write($outputString, false, $verbosity | OutputInterface::OUTPUT_RAW);
    }
}
