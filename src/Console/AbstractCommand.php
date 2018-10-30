<?php declare(strict_types=1);
/**
 * (c) Pluswerk AG
 */

namespace PHPSu\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AbstractCommand extends Command
{
    /**
     * @var OutputInterface $input
     */
    protected $output;

    /**
     * @var InputInterface $output
     */
    protected $input;

    public function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->input = $input;
        $this->output = $output;
    }
}
