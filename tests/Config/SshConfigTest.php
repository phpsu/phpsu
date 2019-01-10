<?php
declare(strict_types=1);

namespace PHPSu\Tests\Config;

use PHPSu\Config\SshConfig;
use PHPSu\Config\SshConfigHost;
use PHPUnit\Framework\TestCase;

class SshConfigTest extends TestCase
{

    public function testWriteConfig()
    {
        $sshConfig = new SshConfig();
        $sshConfig->setFile($file = new \SplTempFileObject());
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
        $this->assertSame($expectedSshConfigString, implode('', iterator_to_array($file)));
    }
}
