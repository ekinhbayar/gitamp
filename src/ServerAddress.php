<?php declare(strict_types=1);

namespace ekinhbayar\GitAmp;

final class ServerAddress
{
    private string $ipAddress;

    private int $port;

    public function __construct(string $ipAddress, int $port)
    {
        $this->ipAddress = $ipAddress;
        $this->port      = $port;
    }

    public function getUri(): string
    {
        return sprintf('%s:%d', $this->ipAddress, $this->port);
    }
}
