<?php declare(strict_types=1);

namespace ekinhbayar\GitAmpTests;

use ekinhbayar\GitAmp\ServerAddress;
use PHPUnit\Framework\TestCase;

class ServerAddressTest extends TestCase
{
    public function testGetUri(): void
    {
        $this->assertSame('127.0.0.1:8080', (new ServerAddress('127.0.0.1', 8080))->getUri());
    }
}
