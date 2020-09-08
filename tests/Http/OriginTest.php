<?php declare(strict_types=1);

namespace ekinhbayar\GitAmpTests\Http;

use ekinhbayar\GitAmp\Http\Origin;
use PHPUnit\Framework\TestCase;

class OriginTest extends TestCase
{
    public function testGetHttpOnStandardPort(): void
    {
        $origin = new Origin([
            'hostname' => 'gitamp.audio',
            'expose'   => ['port' => 80],
        ]);

        $this->assertSame('http://gitamp.audio', $origin->get());
    }

    public function testGetHttpOnNonStandardPort(): void
    {
        $origin = new Origin([
            'hostname' => 'gitamp.audio',
            'expose'   => ['port' => 8080],
        ]);

        $this->assertSame('http://gitamp.audio:8080', $origin->get());
    }

    public function testGetHttpsOnStandardPort(): void
    {
        $origin = new Origin([
            'hostname' => 'gitamp.audio',
            'ssl'      => ['port' => 443],
        ]);

        $this->assertSame('https://gitamp.audio', $origin->get());
    }

    public function testGetHttpsOnNonStandardPort(): void
    {
        $origin = new Origin([
            'hostname' => 'gitamp.audio',
            'ssl'      => ['port' => 1443],
        ]);

        $this->assertSame('https://gitamp.audio:1443', $origin->get());
    }
}
