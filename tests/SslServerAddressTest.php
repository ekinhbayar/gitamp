<?php declare(strict_types=1);

namespace ekinhbayar\GitAmpTests;

use Amp\Socket\Certificate;
use PHPUnit\Framework\TestCase;
use ekinhbayar\GitAmp\SslServerAddress;

class SslServerAddressTest extends TestCase
{
    public function testGetUri(): void
    {
        $this->assertSame(
            '127.0.0.1:8080',
            (new SslServerAddress('127.0.0.1', 8080, new Certificate('/test/cert.pem')))->getUri(),
        );
    }

    public function testGetCertificate(): void
    {
        $this->assertInstanceOf(
            Certificate::class,
            (new SslServerAddress('127.0.0.1', 8080, new Certificate('/test/cert.pem')))->getCertificate(),
        );

        $this->assertSame(
            '/test/cert.pem',
            (new SslServerAddress('127.0.0.1', 8080, new Certificate('/test/cert.pem')))
                ->getCertificate()
                ->getCertFile(),
        );
    }
}
