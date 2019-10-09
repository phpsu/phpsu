<?php

declare(strict_types=1);

namespace PHPSu\Tests\TestHelper;

use Symfony\Component\Console\Formatter\OutputFormatterInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\Console\Output\ConsoleSectionOutput;

/**
 * This Class is mainly copied from \Symfony\Component\Console\Output\ConsoleSectionOutput
 * It is used for Testing only.
 * {@inheritdoc}
 */
final class BufferedConsoleOutput extends StreamOutput implements ConsoleOutputInterface
{
    private $stderr;
    private $consoleSectionOutputs = [];

    /**
     * @param int                           $verbosity The verbosity level (one of the VERBOSITY constants in OutputInterface)
     * @param bool|null                     $decorated Whether to decorate messages (null for auto-guessing)
     * @param OutputFormatterInterface|null $formatter Output formatter instance (null to use default OutputFormatter)
     */
    public function __construct(int $verbosity = self::VERBOSITY_NORMAL, bool $decorated = null, OutputFormatterInterface $formatter = null)
    {
        parent::__construct($this->openOutputStream(), $verbosity, $decorated, $formatter);

        $actualDecorated = $this->isDecorated();
        $this->stderr = new StreamOutput($this->openErrorStream(), $verbosity, $decorated, $this->getFormatter());

        if (null === $decorated) {
            $this->setDecorated($actualDecorated && $this->stderr->isDecorated());
        }
    }

    /**
     * Creates a new output section.
     */
    public function section(): ConsoleSectionOutput
    {
        return new ConsoleSectionOutput($this->getStream(), $this->consoleSectionOutputs, $this->getVerbosity(), $this->isDecorated(), $this->getFormatter());
    }

    /**
     * {@inheritdoc}
     */
    public function setDecorated($decorated)
    {
        parent::setDecorated($decorated);
        $this->stderr->setDecorated($decorated);
    }

    /**
     * {@inheritdoc}
     */
    public function setFormatter(OutputFormatterInterface $formatter)
    {
        parent::setFormatter($formatter);
        $this->stderr->setFormatter($formatter);
    }

    /**
     * {@inheritdoc}
     */
    public function setVerbosity($level)
    {
        parent::setVerbosity($level);
        $this->stderr->setVerbosity($level);
    }

    /**
     * {@inheritdoc}
     */
    public function getErrorOutput()
    {
        return $this->stderr;
    }

    /**
     * {@inheritdoc}
     */
    public function setErrorOutput(OutputInterface $error)
    {
        $this->stderr = $error;
    }

    /**
     * @return resource
     */
    private function openOutputStream()
    {
        return fopen('php://memory', 'rw+');
    }

    /**
     * @return resource
     */
    private function openErrorStream()
    {
        return fopen('php://memory', 'rw+');
    }
}
