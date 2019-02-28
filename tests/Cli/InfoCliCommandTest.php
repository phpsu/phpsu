<?php
declare(strict_types=1);


namespace PHPSu\Tests\Cli;

use PHPSu\Cli\InfoCliCommand;
use PHPSu\Config\ConfigurationLoader;
use PHPSu\Controller;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class InfoCliCommandTest extends TestCase
{
    public function testPrintDependencyVersionsLocally(): void
    {
        $command = new InfoCliCommand(new ConfigurationLoader(), new Controller());

        $commandTester = new CommandTester($command);
        $commandTester->execute([]);
        $result =  $commandTester->getDisplay(true);
        foreach (['rsync', 'ssh', 'mysqldump', 'mysql-distribution', 'locally', 'installed'] as $string) {
            $this->assertContains($string, $result, '', true);
        }
    }
}
