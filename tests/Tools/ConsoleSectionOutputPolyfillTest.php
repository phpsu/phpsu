<?php
declare(strict_types=1);


namespace PHPSu\Tests\Tools;

use PHPSu\Tools\ConsolePolyfill\ConsoleSectionOutput;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\TesterTrait;

class ConsoleSectionOutputPolyfillTest extends TestCase
{
    use TesterTrait;

    public function testConsoleSectionOutputPolyfill(): void
    {
        $sectionOutputs = [];
        $this->initOutput(['decorated' => true]);
        $this->output->writeln('output');
        $section = new ConsoleSectionOutput($this->output->getStream(), $sectionOutputs, $this->output->getVerbosity(), $this->output->isDecorated(), $this->output->getFormatter());
        $section->writeln('sectionwriteln');
        $this->assertContains('output', $this->getDisplay());
        $this->assertContains('sectionwriteln', $this->getDisplay());
        $section->overwrite('hello');
        $this->assertEquals("output\nsectionwriteln\n\e[1A\e[0Jhello\n", $this->getDisplay());
    }
}
