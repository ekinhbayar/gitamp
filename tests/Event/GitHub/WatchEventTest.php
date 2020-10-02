<?php declare(strict_types=1);

namespace ekinhbayar\GitAmpTests\Event\GitHub;

use ekinhbayar\GitAmp\Event\GitHub\WatchEvent;
use PHPUnit\Framework\TestCase;

class WatchEventTest extends TestCase
{
    private array $event;

    private array $assertEvent;

    public function setUp(): void
    {
        $this->event = [
            'id'      => 1,
            'repo'    => ['name' => 'test/repo'],
            'actor'   => ['login' => 'PeeHaa'],
            'payload' => ['action' => 'The action'],
        ];

        $this->assertEvent = [
            'id'          => 1,
            'type'        => 7,
            'information' => [
                'url'     => 'https://github.com/test/repo',
                'payload' => 'not sure if stupid but works anyway',
                'message' => 'PeeHaa watched test/repo',
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
        $event = new WatchEvent($this->event, []);

        $this->assertSame($this->assertEvent, $event->getAsArray());
    }

    public function testGetAsArrayEgg(): void
    {
        $this->event['repo']['name'] = 'ekinhbayar/gitamp';

        $this->assertEvent['information']['url']     = 'https://github.com/ekinhbayar/gitamp';
        $this->assertEvent['information']['message'] = 'PeeHaa watched ekinhbayar/gitamp';
        $this->assertEvent['sound']['type']          = 'SwellEgg';

        $event = new WatchEvent($this->event, ['ekinhbayar/gitamp']);

        $this->assertSame($this->assertEvent, $event->getAsArray());
    }
}
