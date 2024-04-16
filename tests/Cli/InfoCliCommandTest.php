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
