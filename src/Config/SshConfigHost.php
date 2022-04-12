<?php

declare(strict_types=1);

namespace PHPSu\Config;

/**
 * @internal
 * @property string $AddKeysToAgent
 * @property string $AddressFamily
 * @property string $BatchMode
 * @property string $BindAddress
 * @property string $BindInterface
 * @property string $CASignatureAlgorithms
 * @property string $CanonicalDomains
 * @property string $CanonicalizeFallbackLocal
 * @property string $CanonicalizeHostname
 * @property string $CanonicalizeMaxDots
 * @property string $CanonicalizePermittedCNAMEs
 * @property string $CertificateFile
 * @property string $ChallengeResponseAuthentication
 * @property string $CheckHostIP
 * @property string $Ciphers
 * @property string $ClearAllForwardings
 * @property string $Compression
 * @property string $ConnectTimeout
 * @property string $ConnectionAttempts
 * @property string $ControlMaster
 * @property string $ControlPath
 * @property string $ControlPersist
 * @property string $DynamicForward
 * @property string $EnableSSHKeysign
 * @property string $EscapeChar
 * @property string $ExitOnForwardFailure
 * @property string $FingerprintHash
 * @property string $ForwardAgent
 * @property string $ForwardX11
 * @property string $ForwardX11Timeout
 * @property string $ForwardX11Trusted
 * @property string $GSSAPIAuthentication
 * @property string $GSSAPIDelegateCredentials
 * @property string $GatewayPorts
 * @property string $GlobalKnownHostsFile
 * @property string $HashKnownHosts
 * @property string $Host
 * @property string $HostKeyAlgorithms
 * @property string $HostKeyAlias
 * @property string $HostName
 * @property string $HostbasedAuthentication
 * @property string $HostbasedKeyTypes
 * @property string $IPQoS
 * @property string $IdentitiesOnly
 * @property string $IdentityAgent
 * @property string $IdentityFile
 * @property string $IgnoreUnknown
 * @property string $Include
 * @property string $KbdInteractiveAuthentication
 * @property string $KbdInteractiveDevices
 * @property string $KexAlgorithms
 * @property string $LocalCommand
 * @property string $LocalForward
 * @property string $LogLevel
 * @property string $MACs
 * @property string $Match
 * @property string $NoHostAuthenticationForLocalhost
 * @property string $NumberOfPasswordPrompts
 * @property string $PKCS11Provider
 * @property string $PasswordAuthentication
 * @property string $PermitLocalCommand
 * @property int $Port
 * @property string $PreferredAuthentications
 * @property string $ProxyCommand
 * @property string $ProxyJump
 * @property string $ProxyUseFdpass
 * @property string $PubkeyAcceptedKeyTypes
 * @property string $PubkeyAuthentication
 * @property string $RekeyLimit
 * @property string $RemoteCommand
 * @property string $RemoteForward
 * @property string $RequestTTY
 * @property string $RevokedHostKeys
 * @property string $SendEnv
 * @property string $ServerAliveCountMax
 * @property string $ServerAliveInterval
 * @property string $SetEnv
 * @property string $StreamLocalBindMask
 * @property string $StreamLocalBindUnlink
 * @property string $StrictHostKeyChecking
 * @property string $SyslogFacility
 * @property string $TCPKeepAlive
 * @property string $Tunnel
 * @property string $TunnelDevice
 * @property string $UpdateHostKeys
 * @property string $User
 * @property string $UserKnownHostsFile
 * @property string $VerifyHostKeyDNS
 * @property string $VisualHostKey
 * @property string $XAuthLocation
 */
final class SshConfigHost
{
    /** @var array<string, string> */
    private array $options = [];

    public function __isset(string $name): bool
    {
        return isset($this->options[$name]);
    }

    public function __get(string $name): string
    {
        return $this->options[$name];
    }

    /**
     * @param string $name
     * @param string|int $config
     * @return void
     */
    public function __set(string $name, $config): void
    {
        $this->options[$name] = (string)$config;
    }

    /**
     * @return array<string, string>
     */
    public function getConfig(): array
    {
        ksort($this->options);
        return $this->options;
    }
}
