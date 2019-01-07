<?php
declare(strict_types=1);

namespace PHPSu\Beta;

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
        $prefix = $output->getFormatter()->format(sprintf('<fg=%s>%s:</> ', $color, $process->getName()));
        $output->writeln($prefix . str_replace(PHP_EOL, PHP_EOL . $prefix, trim($data)), $verbosity | OutputInterface::OUTPUT_RAW);
    }
}
