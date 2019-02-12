<?php
declare(strict_types=1);

namespace PHPSu\Tests\Cli;

use PHPSu\Cli\PhpsuApplication;
use PHPSu\Cli\SshCliCommand;
use PHPSu\Cli\SyncCliCommand;
use PHPUnit\Framework\TestCase;

class PhpsuApplicationTest extends TestCase
{
    public function testCommand()
    {
        $app = PhpsuApplication::createApplication();
        $this->assertInstanceOf(SyncCliCommand::class, $app->get('sync'));
        $this->assertInstanceOf(SshCliCommand::class, $app->get('ssh'));
    }
}
