<?php declare(strict_types=1);

namespace ekinhbayar\GitAmpTests\Event\GitHub;

use ekinhbayar\GitAmp\Event\GitHub\ForkEvent;
use PHPUnit\Framework\TestCase;

class ForkEventTest extends TestCase
{
    private array $event;

    private array $assertEvent;

    public function setUp(): void
    {
        $this->event = [
            'id'    => 1,
            'repo'  => ['name' => 'test/repo'],
            'actor' => ['login' => 'PeeHaa'],
        ];

        $this->assertEvent = [
            'id'          => 1,
            'type'        => 5,
            'information' => [
                'url'     => 'https://github.com/test/repo',
                'payload' => 'not sure if stupid but works anyway',
                'message' => 'PeeHaa forked test/repo',
            ],
            'ring'        => [
                'animationDuration' => 3000,
                'radius'            => 80,
            ],
            'sound'       => [
                'size' => 1.0,
                'type' => 'Swell',
            ],
        ];
    }

    public function testGetAsArray(): void
    {
        $event = new ForkEvent($this->event, []);

        $this->assertSame($this->assertEvent, $event->getAsArray());
    }

    public function testGetAsArrayEgg(): void
    {
        $this->event['repo']['name'] = 'ekinhbayar/gitamp';

        $this->assertEvent['information']['url']     = 'https://github.com/ekinhbayar/gitamp';
        $this->assertEvent['information']['message'] = 'PeeHaa forked ekinhbayar/gitamp';
        $this->assertEvent['sound']['type']          = 'SwellEgg';

        $event = new ForkEvent($this->event, ['ekinhbayar/gitamp']);

        $this->assertSame($this->assertEvent, $event->getAsArray());
    }
}
