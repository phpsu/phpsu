<?php
declare(strict_types=1);

namespace PHPSu\Process;

use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class OutputCallback
{
    /** @var OutputInterface */
    private $output;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

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
            if (strlen($line) > 80) {
                $line = substr($line, 0, 80) . $formatter->format('<fg=yellow>...</> ');
            }
            $outputString .= $prefix . $line . PHP_EOL;
        }
        $output->write($outputString, false, $verbosity | OutputInterface::OUTPUT_RAW);
    }
}
