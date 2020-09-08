<?php declare(strict_types=1);

namespace ekinhbayar\GitAmpTests\Event\GitHub;

use ekinhbayar\GitAmp\Event\GitHub\PushEvent;
use PHPUnit\Framework\TestCase;

class PushEventTest extends TestCase
{
    private array $event;

    private array $assertEvent;

    public function setUp(): void
    {
        $this->event = [
            'id'      => 1,
            'repo'    => ['name' => 'test/repo'],
            'actor'   => ['login' => 'PeeHaa'],
            'payload' => [
                'commits' => [['message' => 'Commit message']],
            ],
        ];

        $this->assertEvent = [
            'id'          => 1,
            'type'        => 1,
            'information' => [
                'url'     => 'https://github.com/test/repo',
                'payload' => 'Commit message',
                'message' => 'PeeHaa pushed to test/repo',
            ],
            'ring'        => [
                'animationDuration' => 3000,
                'radius'            => 80,
            ],
            'sound'       => [
                'size' => strlen('Commit message') * 1.1,
                'type' => 'Celesta',
            ],
        ];
    }

    public function testGetAsArrayWithCommitMessage(): void
    {
        $event = new PushEvent($this->event);

        $this->assertSame($this->assertEvent, $event->getAsArray());
    }

    public function testGetAsArrayWithoutCommitMessage(): void
    {
        unset($this->event['payload']['commits'][0]['message']);

        $this->assertEvent['information']['payload'] = 'https://github.com/PeeHaa';
        $this->assertEvent['sound']['size']          = strlen('https://github.com/PeeHaa') * 1.1;

        $event = new PushEvent($this->event);

        $this->assertSame($this->assertEvent, $event->getAsArray());
    }

    public function testGetAsArrayWithCommitMessageEgg(): void
    {
        $this->event['repo']['name'] = 'ekinhbayar/gitamp';

        $this->assertEvent['information']['url']     = 'https://github.com/ekinhbayar/gitamp';
        $this->assertEvent['information']['message'] = 'PeeHaa pushed to ekinhbayar/gitamp';
        $this->assertEvent['sound']['type']          = 'CelestaEgg';
        $this->assertEvent['sound']['size']          = 1.0;

        $event = new PushEvent($this->event);

        $this->assertSame($this->assertEvent, $event->getAsArray());
    }

    public function testGetAsArrayWithoutCommitMessageEgg(): void
    {
        $this->event['repo']['name'] = 'ekinhbayar/gitamp';

        $this->assertEvent['information']['url']     = 'https://github.com/ekinhbayar/gitamp';
        $this->assertEvent['information']['message'] = 'PeeHaa pushed to ekinhbayar/gitamp';
        $this->assertEvent['sound']['type']          = 'CelestaEgg';

        unset($this->event['payload']['commits'][0]['message']);

        $this->assertEvent['information']['payload'] = 'https://github.com/PeeHaa';
        $this->assertEvent['sound']['size']          = 1.0;

        $event = new PushEvent($this->event);

        $this->assertSame($this->assertEvent, $event->getAsArray());
    }
}
