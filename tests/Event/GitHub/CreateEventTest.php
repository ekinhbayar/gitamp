<?php declare(strict_types=1);

namespace ekinhbayar\GitAmpTests\Event\GitHub;

use ekinhbayar\GitAmp\Event\GitHub\CreateEvent;
use PHPUnit\Framework\TestCase;

class CreateEventTest extends TestCase
{
    private array $event;

    private array $assertEvent;

    public function setUp(): void
    {
        $this->event = [
            'id'      => 1,
            'repo'    => ['name' => 'test/repo'],
            'actor'   => ['login' => 'PeeHaa'],
            'payload' => ['description' => 'The description'],
        ];

        $this->assertEvent = [
            'id'          => 1,
            'type'        => 6,
            'information' => [
                'url'     => 'https://github.com/test/repo',
                'payload' => 'The description',
                'message' => 'PeeHaa created test/repo',
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

    public function testGetAsArrayWithPayloadDescription(): void
    {
        $event = new CreateEvent($this->event);

        $this->assertSame($this->assertEvent, $event->getAsArray());
    }

    public function testGetAsArrayWithoutPayloadDescription(): void
    {
        unset($this->event['payload']['description']);

        $this->assertEvent['information']['payload'] = $this->assertEvent['information']['url'];

        $event = new CreateEvent($this->event);

        $this->assertSame($this->assertEvent, $event->getAsArray());
    }

    public function testGetAsArrayWithPayloadDescriptionEgg(): void
    {
        $this->event['repo']['name'] = 'ekinhbayar/gitamp';

        $this->assertEvent['information']['url']     = 'https://github.com/ekinhbayar/gitamp';
        $this->assertEvent['information']['message'] = 'PeeHaa created ekinhbayar/gitamp';
        $this->assertEvent['sound']['type']          = 'SwellEgg';

        $event = new CreateEvent($this->event);

        $this->assertSame($this->assertEvent, $event->getAsArray());
    }

    public function testGetAsArrayWithoutPayloadDescriptionEgg(): void
    {
        $this->event['repo']['name'] = 'ekinhbayar/gitamp';

        $this->assertEvent['information']['url']     = 'https://github.com/ekinhbayar/gitamp';
        $this->assertEvent['information']['message'] = 'PeeHaa created ekinhbayar/gitamp';
        $this->assertEvent['sound']['type']          = 'SwellEgg';

        unset($this->event['payload']['description']);

        $this->assertEvent['information']['payload'] = $this->assertEvent['information']['url'];

        $event = new CreateEvent($this->event);

        $this->assertSame($this->assertEvent, $event->getAsArray());
    }
}
