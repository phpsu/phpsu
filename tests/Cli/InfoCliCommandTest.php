<?php

declare(strict_types=1);

namespace PHPSu\Tests\Cli;

use PHPSu\Cli\InfoCliCommand;
use PHPSu\Command\CommandGenerator;
use PHPSu\Config\GlobalConfig;
use PHPSu\Controller;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class InfoCliCommandTest extends TestCase
{
    public function testPrintDependencyVersionsLocally(): void
    {
        $config = new GlobalConfig();
        $command = new InfoCliCommand($config, new Controller(new CommandGenerator($config)));

        $commandTester = new CommandTester($command);
        $commandTester->execute([]);
        $result =  $commandTester->getDisplay(true);
        $this->assertSame(0, $commandTester->getStatusCode());
        $this->assertStringMatchesFormat('
List of all dependencies and their versions
===========================================

Locally installed
 -------------------- ----------- --------- 
  Dependency           Installed   Version  
 -------------------- ----------- --------- 
  rsync                ✔           %s
  mysql-distribution   ✔           %s
  mysqldump            ✔           %s
  ssh                  ✔           %s
 -------------------- ----------- --------- 

', $result);
    }
}
