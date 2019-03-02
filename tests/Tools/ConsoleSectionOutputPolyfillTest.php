<?php
declare(strict_types=1);


namespace PHPSu\Tests\Tools;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\ConsoleSectionOutput;
use Symfony\Component\Console\Output\StreamOutput;

class ConsoleSectionOutputPolyfillTest extends TestCase
{
    public function testConsoleSectionOutputPolyfill()
    {
        $sectionOutputs = [];
        $output = new StreamOutput(fopen('php://memory', 'w', false));
        $output->setDecorated(true);
        $output->writeln('output');
        $section = new ConsoleSectionOutput($output->getStream(), $sectionOutputs, $output->getVerbosity(), $output->isDecorated(), $output->getFormatter());
        $section->writeln('sectionwriteln');
        $this->assertContains('output', $this->getDisplay($output));
        $this->assertContains('sectionwriteln', $this->getDisplay($output));
        $section->overwrite('hello');
        $this->assertEquals("output\nsectionwriteln\n\e[1A\e[0Jhello\n", $this->getDisplay($output));
        $this->assertEquals("hello\n", $section->getContent());
    }

    private function getDisplay(StreamOutput $output)
    {
        rewind($output->getStream());
        return stream_get_contents($output->getStream());
    }
}
