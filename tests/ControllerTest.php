<?php
declare(strict_types=1);

namespace PHPSu\Tests;

use PHPSu\Config\GlobalConfig;
use PHPSu\Controller;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\BufferedOutput;

final class ControllerTest extends TestCase
{

    public function testEmptyConfigSshDryRun(): void
    {
        $output = new BufferedOutput();
        $config = new GlobalConfig();
        $config->addAppInstance('production', 'serverEu', '/var/www/prod');
        $config->addAppInstance('local');
        $runner = new Controller($output, $config);
        $runner->ssh('production', '', '', true);
        $this->assertSame("ssh -F '.phpsu/config/ssh_config' 'serverEu' -t 'cd '\''/var/www/prod'\''; bash --login'\n", $output->fetch());
    }

    public function testEmptyConfigSyncDryRun(): void
    {
        $output = new BufferedOutput();
        $config = new GlobalConfig();
        $config->addAppInstance('production', 'serverEu', '/var/www/prod');
        $config->addAppInstance('local');
        $runner = new Controller($output, $config);
        $runner->sync('production', 'local', '', true, false, false, false);
        $this->assertSame('', $output->fetch());
    }
}
