<?php declare(strict_types=1);

namespace ekinhbayar\GitAmpTests;

use Amp\Socket\Certificate;
use ekinhbayar\GitAmp\SslServerAddress;
use League\Uri\Uri;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use ekinhbayar\GitAmp\Configuration;
use ekinhbayar\GitAmp\Github\Token;
use ekinhbayar\GitAmp\ServerAddress;

class ConfigurationTest extends TestCase
{
    private Configuration $configuration;

    public function setUp(): void
    {
        $this->configuration = new Configuration(new Token('12345'));
    }

    public function testDefaultLogLevelAccessors(): void
    {
        $this->assertSame(Logger::INFO, $this->configuration->getLogLevel());
    }

    public function testLogLevelAccessors(): void
    {
        $this->configuration->setLogLevel(Logger::ALERT);

        $this->assertSame(Logger::ALERT, $this->configuration->getLogLevel());
    }

    public function testWebsocketAddressesAccessors(): void
    {
        $this->configuration->addWebsocketAddress(Uri::createFromString('http://example.com'));
        $this->configuration->addWebsocketAddress(Uri::createFromString('http://example.com:1337'));

        $this->assertTrue($this->configuration->websocketAddressExists('http://example.com'));
        $this->assertTrue($this->configuration->websocketAddressExists('http://example.com:1337'));

        $this->assertFalse($this->configuration->websocketAddressExists('http://xample.com'));
    }

    public function testBindAccessors(): void
    {
        $this->configuration->bind(new ServerAddress('127.0.0.1', 80));
        $this->configuration->bind(new ServerAddress('127.0.0.1', 8080));

        $this->assertCount(2, $this->configuration->getServerAddresses());
    }

    public function testBindSslAccessors(): void
    {
        $this->configuration->bindSsl(
            new SslServerAddress('127.0.0.1', 1338, new Certificate('/test/cert.pem')),
        );

        $this->configuration->bindSsl(
            new SslServerAddress('127.0.0.1', 443, new Certificate('/test/cert.pem')),
        );

        $this->assertCount(2, $this->configuration->getSslServerAddresses());
    }

    public function testGetGithubToken(): void
    {
        $this->assertSame('12345', $this->configuration->getGithubToken()->getAuthenticationString());
    }
}
