<?php
declare(strict_types=1);

namespace PHPSu\Tests\Alpha;

use PHPSu\Alpha\SshConfig;
use PHPSu\Alpha\SshConfigGenerator;
use PHPSu\Alpha\SshConfigHost;
use PHPSu\Alpha\SshConnection;
use PHPSu\Alpha\SshConnections;
use PHPUnit\Framework\TestCase;

class SshConfigGeneratorTest extends TestCase
{
    public function testConnectionProblem(): void
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

    public function testGenerate()
    {
        $sshConnections = new SshConnections();
        $sshConnections->add((new SshConnection())->setHost('hostc')
            ->setUrl('user@host_c')
            ->setFrom(['hostb']));

        $sshConnections->add((new SshConnection())->setHost('hostb')
            ->setUrl('user@host_b')
            ->setFrom(['hosta']));

        $sshConnections->add((new SshConnection())->setHost('hosta')
            ->setUrl('user@localhost:2208'));

        $sshConfigGenerator = new SshConfigGenerator();
        $sshConfig = $sshConfigGenerator->generate($sshConnections, 'local');

        $sshConfigExpected = new SshConfig();
        $sshConfigExpected->hostc = new SshConfigHost();
        $sshConfigExpected->hostc->User = 'user';
        $sshConfigExpected->hostc->HostName = 'host_c';
        $sshConfigExpected->hostc->ProxyJump = 'hostb';

        $sshConfigExpected->hostb = new SshConfigHost();
        $sshConfigExpected->hostb->User = 'user';
        $sshConfigExpected->hostb->HostName = 'host_b';
        $sshConfigExpected->hostb->ProxyJump = 'hosta';

        $sshConfigExpected->hosta = new SshConfigHost();
        $sshConfigExpected->hosta->User = 'user';
        $sshConfigExpected->hosta->HostName = 'localhost';
        $sshConfigExpected->hosta->Port = '2208';
        $this->assertEquals($sshConfigExpected, $sshConfig);
    }
}
