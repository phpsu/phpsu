<?php
declare(strict_types=1);

namespace PHPSu\Alpha;

final class RsyncCommand implements CommandInterface
{
    /** @var SshConfig */
    private $sshConfig;
    /** @var string */
    private $options;
    /** @var string */
    private $from;
    /** @var string */
    private $to;

    public static function fromAppInstances(AppInstance $from, AppInstance $to)
    {
        $result = new static();
        $result->from = $from->getHost() . ':' . rtrim($from->getPath(), '/*') . '/*';
        $result->to = $to->getHost() . ':' . $to->getPath();
        return $result;
    }

    public function getSshConfig(): SshConfig
    {
        return $this->sshConfig;
    }

    public function setSshConfig(SshConfig $sshConfig): RsyncCommand
    {
        $this->sshConfig = $sshConfig;
        return $this;
    }

    public function getOptions(): string
    {
        return $this->options;
    }

    public function setOptions(string $options): RsyncCommand
    {
        $this->options = $options;
        return $this;
    }

    public function getFrom(): string
    {
        return $this->from;
    }

    public function setFrom(string $from): RsyncCommand
    {
        $this->from = $from;
        return $this;
    }

    public function getTo(): string
    {
        return $this->to;
    }

    public function setTo(string $to): RsyncCommand
    {
        $this->to = $to;
        return $this;
    }

    public function generate(): string
    {
        $this->sshConfig->writeConfig($file = new TempSshConfigFile());
        return 'rsync ' . $this->options . ' -e "ssh -F ' . $file->getPathname() . '" ' . $this->from . ' ' . $this->to;
    }
}
