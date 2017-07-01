<?php declare(strict_types = 1);

namespace ekinhbayar\GitAmpTests\Events\Type;

use ekinhbayar\GitAmp\Events\Type\IssuesEvent;
use PHPUnit\Framework\TestCase;

class IssuesEventTest extends TestCase
{
    private $event;

    private $assertEvent;

    public function setUp()
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
            'id'        => 1,
            'type'      => 3,
            'information' => [
                'url' => 'http://example.com',
                'payload' => 'Issue title',
                'message' => 'PeeHaa The action an issue in test/repo',
            ],
            'ring'  => [
                'animationDuration' => 3000,
                'radius'            => 80,
            ],
            'sound' => [
                'size' => strlen('Issue title') * 1.1,
                'type' => 'Clav',
            ],
        ];
    }

    public function testGetAsArray()
    {
        $event = new IssuesEvent($this->event);

        $this->assertSame($this->assertEvent, $event->getAsArray());
    }

    public function testGetAsArrayEgg()
    {
        $this->event['repo']['name'] = 'ekinhbayar/gitamp';

        $this->assertEvent['information']['url']     = 'http://example.com';
        $this->assertEvent['information']['message'] = 'PeeHaa The action an issue in ekinhbayar/gitamp';
        $this->assertEvent['sound']['type']          = 'ClavEgg';
        $this->assertEvent['sound']['size']          = 1.0;

        $event = new IssuesEvent($this->event);

        $this->assertSame($this->assertEvent, $event->getAsArray());
    }
}
