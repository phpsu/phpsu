<?php
declare(strict_types=1);

namespace PHPSu\Alpha;

final class SshCommand implements CommandInterface
{
    /** @var SshConfig */
    private $sshConfig;
    /** @var string */
    private $into;

    public static function fromGlobal(GlobalConfig $global, string $connectionName, string $currentHost): SshCommand
    {
        $host = '';
        if (isset($global->appInstances->{$connectionName})) {
            /** @var AppInstance $app */
            $app = $global->appInstances->{$connectionName};
            $host = $app->getHost();
        }
        if ($host === '') {
            $host = $connectionName;
        }
        if ($currentHost === $host) {
            throw new \Exception(sprintf('the found host and the current Host are the same: %s', $host));
        }
        $result = new static();
        $result->setInto($host);
        return $result;
    }

    public function getSshConfig(): SshConfig
    {
        return $this->sshConfig;
    }

    public function setSshConfig(SshConfig $sshConfig): SshCommand
    {
        $this->sshConfig = $sshConfig;
        return $this;
    }

    public function getInto(): string
    {
        return $this->into;
    }

    public function setInto(string $into): SshCommand
    {
        $this->into = $into;
        return $this;
    }

    public function generate(): string
    {
        $file = $this->sshConfig->getFile();
        return 'ssh -F ' . $file->getPathname() . ' ' . $this->into;
    }
}
