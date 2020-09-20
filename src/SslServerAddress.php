<?php declare(strict_types=1);

namespace ekinhbayar\GitAmp;

use Amp\Socket\Certificate;

final class SslServerAddress
{
    private string $ipAddress;

    private int $port;

    private Certificate $certificate;

    public function __construct(string $ipAddress, int $port, Certificate $certificate)
    {
        $this->ipAddress   = $ipAddress;
        $this->port        = $port;
        $this->certificate = $certificate;
    }

    public function getUri(): string
    {
        return sprintf('%s:%d', $this->ipAddress, $this->port);
    }

    public function getCertificate(): Certificate
    {
        return $this->certificate;
    }
}
