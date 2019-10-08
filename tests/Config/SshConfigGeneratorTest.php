<?php

declare(strict_types=1);

namespace PHPSu\Tests\Config;

use PHPSu\Config\SshConfig;
use PHPSu\Config\SshConfigGenerator;
use PHPSu\Config\SshConfigHost;
use PHPSu\Config\SshConnection;
use PHPSu\Config\SshConnections;
use PHPUnit\Framework\TestCase;

final class SshConfigGeneratorTest extends TestCase
{
    public function testConnectionProblem()
    {
        // from everywhere
        $sshConnections = new SshConnections();
        $sshConnections->addConnections(...[
            $toA = (new SshConnection())->setHost('hosta')->setFrom([]),
            $toB = (new SshConnection())->setHost('hostb')->setFrom(['hosta']),
            $toCFromB = (new SshConnection())->setHost('hostc')->setFrom(['hostb']),
            $toCFromA = (new SshConnection())->setHost('hostc')->setFrom(['hosta']),
        ]);

        $sshConfigGenerator = new SshConfigGenerator();
        $paths = $sshConfigGenerator->findAllPaths('local', 'hostc', $sshConnections);
        $this->assertEquals([
            [$toA, $toB, $toCFromB],
            [$toA, $toCFromA],
        ], $paths);
        $path = $sshConfigGenerator->findShortestPath('local', 'hostc', $sshConnections);
        $this->assertEquals([$toA, $toCFromA], $path);
    }

    public function testSshConfigGeneratorGenerate()
    {
        $sshConnections = new SshConnections();
        $sshConnections->add(
            (new SshConnection())->setHost('hostc')
            ->setUrl('user@host_c')
            ->setFrom(['hostb'])
        );

        $sshConnections->add(
            (new SshConnection())->setHost('hostb')
            ->setUrl('user@host_b')
            ->setFrom(['hosta'])
            ->setOptions(['ForwardAgent' => 'no'])
        );

        $sshConnections->add(
            (new SshConnection())->setHost('hosta')
            ->setUrl('user@localhost:2208')
        );

        $sshConfigGenerator = new SshConfigGenerator();
        $sshConfig = $sshConfigGenerator->generate($sshConnections, ['ForwardAgent' => 'yes'], 'local');

        $sshConfigExpected = new SshConfig();
        $sshConfigExpected->hostc = new SshConfigHost();
        $sshConfigExpected->hostc->User = 'user';
        $sshConfigExpected->hostc->HostName = 'host_c';
        $sshConfigExpected->hostc->ProxyJump = 'hostb';

        $sshConfigExpected->hostb = new SshConfigHost();
        $sshConfigExpected->hostb->User = 'user';
        $sshConfigExpected->hostb->HostName = 'host_b';
        $sshConfigExpected->hostb->ProxyJump = 'hosta';
        $sshConfigExpected->hostb->ForwardAgent = 'no';

        $sshConfigExpected->hosta = new SshConfigHost();
        $sshConfigExpected->hosta->User = 'user';
        $sshConfigExpected->hosta->HostName = 'localhost';
        $sshConfigExpected->hosta->Port = 2208;

        $sshConfigExpected->{'*'} = new SshConfigHost();
        $sshConfigExpected->{'*'}->ForwardAgent = 'yes';
        $this->assertEquals($sshConfigExpected, $sshConfig);
    }

    public function testSshConfigGeneratorGenerateError()
    {
        $sshConnections = new SshConnections();
        $sshConnections->add((new SshConnection())->setHost('hostb')
            ->setUrl('user@host_b')
            ->setFrom(['hosta', 'hostb']));
        $this->expectExceptionMessage('the source and destination Host can not be the same: hostb');
        $sshConnections->compile();
    }

    public function testSshConfigGeneratorGenerateWithMultipleFrom()
    {
        $sshConnections = new SshConnections();
        $sshConnections->add((new SshConnection())->setHost('hostc')
            ->setUrl('user@host_c')
            ->setFrom(['hostb', 'hosta']));

        $sshConnections->add((new SshConnection())->setHost('hostb')
            ->setUrl('user@host_b')
            ->setFrom(['hosta'])
            ->setOptions(['ForwardAgent' => 'no']));

        $sshConnections->add((new SshConnection())->setHost('hosta')
            ->setUrl('user@localhost:2208'));

        $sshConfigGenerator = new SshConfigGenerator();
        $sshConfig = $sshConfigGenerator->generate($sshConnections, ['ForwardAgent' => 'yes'], 'local');

        $sshConfigExpected = new SshConfig();
        $sshConfigExpected->hostc = new SshConfigHost();
        $sshConfigExpected->hostc->User = 'user';
        $sshConfigExpected->hostc->HostName = 'host_c';
        $sshConfigExpected->hostc->ProxyJump = 'hosta';

        $sshConfigExpected->hostb = new SshConfigHost();
        $sshConfigExpected->hostb->User = 'user';
        $sshConfigExpected->hostb->HostName = 'host_b';
        $sshConfigExpected->hostb->ProxyJump = 'hosta';
        $sshConfigExpected->hostb->ForwardAgent = 'no';

        $sshConfigExpected->hosta = new SshConfigHost();
        $sshConfigExpected->hosta->User = 'user';
        $sshConfigExpected->hosta->HostName = 'localhost';
        $sshConfigExpected->hosta->Port = 2208;

        $sshConfigExpected->{'*'} = new SshConfigHost();
        $sshConfigExpected->{'*'}->ForwardAgent = 'yes';
        $this->assertEquals($sshConfigExpected, $sshConfig);
    }
}
