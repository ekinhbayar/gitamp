<?php declare(strict_types=1);

namespace ekinhbayar\GitAmp;

use ekinhbayar\GitAmp\Github\Token;
use League\Uri\Contracts\UriInterface;
use Monolog\Logger;

final class Configuration
{
    private int $logLevel = Logger::INFO;

    /** @var array<UriInterface> */
    private array $websocketAddresses = [];

    /** @var array<ServerAddress> */
    private array $bind = [];

    /** @var array<string> */
    private array $specialRepositories = [];

    private Token $githubToken;

    public function __construct(Token $githubToken)
    {
        $this->githubToken = $githubToken;
    }

    public function setLogLevel(int $logLevel): self
    {
        $this->logLevel = $logLevel;

        return $this;
    }

    public function getLogLevel(): int
    {
        return $this->logLevel;
    }

    public function addWebsocketAddress(UriInterface $address): self
    {
        $this->websocketAddresses[] = $address;

        return $this;
    }

    public function websocketAddressExists(string $origin): bool
    {
        foreach ($this->websocketAddresses as $websocketAddress) {
            if ((string) $websocketAddress === $origin) {
                return true;
            }
        }

        return false;
    }

    public function bind(ServerAddress $address): self
    {
        $this->bind[] = $address;

        return $this;
    }

    /**
     * @return array<ServerAddress>
     */
    public function getServerAddresses(): array
    {
        return $this->bind;
    }

    public function addSpecialRepository(string $repository): self
    {
        $this->specialRepositories[] = $repository;

        return $this;
    }

    /**
     * @return array<string>
     */
    public function getSpecialRepositories(): array
    {
        return $this->specialRepositories;
    }

    public function getGithubToken(): Token
    {
        return $this->githubToken;
    }
}
