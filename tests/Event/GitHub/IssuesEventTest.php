<?php declare(strict_types=1);

namespace ekinhbayar\GitAmpTests\Event\GitHub;

use ekinhbayar\GitAmp\Event\GitHub\IssuesEvent;
use PHPUnit\Framework\TestCase;

class IssuesEventTest extends TestCase
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
                'action' => 'The action',
                'issue'  => ['html_url' => 'http://example.com', 'title' => 'Issue title'],
            ],
        ];

        $this->assertEvent = [
            'id'          => 1,
            'type'        => 3,
            'information' => [
                'url'     => 'http://example.com',
                'payload' => 'Issue title',
                'message' => 'PeeHaa The action an issue in test/repo',
            ],
            'ring'        => [
                'animationDuration' => 3000,
                'radius'            => 80,
            ],
            'sound'       => [
                'size' => strlen('Issue title') * 1.1,
                'type' => 'Clav',
            ],
        ];
    }

    public function testGetAsArray(): void
    {
        $event = new IssuesEvent($this->event, []);

        $this->assertSame($this->assertEvent, $event->getAsArray());
    }

    public function testGetAsArrayEgg(): void
    {
        $this->event['repo']['name'] = 'ekinhbayar/gitamp';

        $this->assertEvent['information']['url']     = 'http://example.com';
        $this->assertEvent['information']['message'] = 'PeeHaa The action an issue in ekinhbayar/gitamp';
        $this->assertEvent['sound']['type']          = 'ClavEgg';
        $this->assertEvent['sound']['size']          = 1.0;

        $event = new IssuesEvent($this->event, ['ekinhbayar/gitamp']);

        $this->assertSame($this->assertEvent, $event->getAsArray());
    }
}
