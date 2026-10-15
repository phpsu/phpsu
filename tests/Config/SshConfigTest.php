<?php

declare(strict_types=1);

namespace PHPSu\Tests\Config;

use PHPSu\Config\SshConfig;
use PHPSu\Config\SshConfigHost;
use PHPSu\Tests\Command\CommandGeneratorTest;
use PHPUnit\Framework\TestCase;
use SplTempFileObject;

final class SshConfigTest extends TestCase
{
    public function testWriteConfig(): void
    {
        $sshConfig = new SshConfig();
        $file = new SplTempFileObject();
        $file->fwrite('This should be overwritten');

        $sshConfig->setFile($file);
        $sshConfig->hostc = new SshConfigHost();
        $sshConfig->hostc->User = 'user';
        $sshConfig->hostc->HostName = 'host_c';
        $sshConfig->hostc->ProxyJump = 'hostb';

        $sshConfig->hostb = new SshConfigHost();
        $sshConfig->hostb->User = 'user';
        $sshConfig->hostb->HostName = 'host_b';
        $sshConfig->hostb->ProxyJump = 'hosta';

        $sshConfig->hosta = new SshConfigHost();
        $sshConfig->hosta->User = 'user';
        $sshConfig->hosta->HostName = 'localhost';
        $sshConfig->hosta->Port = 2208;

        $sshConfig->{'*'} = new SshConfigHost();
        $sshConfig->{'*'}->StrictHostKeyChecking = 'no';
        $sshConfig->{'*'}->UserKnownHostsFile = '/dev/null';
        $sshConfig->{'*'}->IdentityFile = './docker/testCaseD/id_rsa';

        $sshConfig->writeConfig();

        $expectedSshConfigString = <<<'SSH_CONFIG'
Host hostc
  HostName host_c
  ProxyJump hostb
  User user

Host hostb
  HostName host_b
  ProxyJump hosta
  User user

Host hosta
  HostName localhost
  Port 2208
  User user

Host *
  IdentityFile ./docker/testCaseD/id_rsa
  StrictHostKeyChecking no
  UserKnownHostsFile /dev/null


SSH_CONFIG;
        $this->assertSame($expectedSshConfigString, CommandGeneratorTest::implodeTempFile($file));
    }

    public function testWriteConfigWithIncludes(): void
    {
        $sshConfig = new SshConfig();
        $file = new SplTempFileObject();
        $sshConfig->setFile($file);

        $sshConfig->{'*'} = new SshConfigHost();
        $sshConfig->{'*'}->ForwardAgent = 'yes';

        $sshConfig->hosta = new SshConfigHost();
        $sshConfig->hosta->HostName = 'host_a';

        $sshConfig->addInclude('/project/ssh/config');
        $sshConfig->addInclude('/project/path with space/config');
        $this->assertSame(['/project/ssh/config', '/project/path with space/config'], $sshConfig->getIncludes());

        $sshConfig->writeConfig();

        $expectedSshConfigString = <<<'SSH_CONFIG'
Host hosta
  HostName host_a

Match all
  Include /project/ssh/config
  Include "/project/path with space/config"

Host *
  ForwardAgent yes


SSH_CONFIG;
        $this->assertSame($expectedSshConfigString, CommandGeneratorTest::implodeTempFile($file));
    }
}
