<?php
declare(strict_types=1);

namespace PHPSu\Tests\Charlie;

use PHPSu\Alpha\AppInstance;
use PHPSu\Alpha\GlobalConfig;
use PHPSu\Charlie\Runner;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\BufferedOutput;

class RunnerTest extends TestCase
{
    public function testSync(): void
    {
        $output = new BufferedOutput();
        $config = new GlobalConfig();
        $config->addAppInstance('production', 'serverEu', '/var/www/prod');
        $config->addAppInstance('local');
        $runner = new Runner($output, $config);
        $runner->sync('production', 'local', '', true);
        $this->assertSame("+------+--------------+\n| Name | Bash Command |\n+------+--------------+\n", $output->fetch());
    }
}
