<?php declare(strict_types = 1);

namespace ekinhbayar\GitAmpTests\Events\Type;

use ekinhbayar\GitAmp\Events\Type\PullRequestEvent;
use PHPUnit\Framework\TestCase;

class PullRequestEventTest extends TestCase
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
                'action'       => 'The action',
                'pull_request' => ['html_url' => 'http://example.com', 'title' => 'PR title'],
            ],
        ];

        $this->assertEvent = [
            'id'        => 1,
            'type'      => 2,
            'information' => [
                'url' => 'http://example.com',
                'payload' => 'PR title',
                'message' => 'PeeHaa The action a PR for test/repo',
            ],
            'ring'  => [
                'animationDuration' => 10000,
                'radius'            => 600,
            ],
            'sound' => [
                'size' => 1.0,
                'type' => 'Swell',
            ],
        ];
    }

    public function testGetAsArray()
    {
        $event = new PullRequestEvent($this->event);

        $this->assertSame($this->assertEvent, $event->getAsArray());
    }

    public function testGetAsArrayEgg()
    {
        $this->event['repo']['name'] = 'ekinhbayar/gitamp';

        $this->assertEvent['information']['url']     = 'http://example.com';
        $this->assertEvent['information']['message'] = 'PeeHaa The action a PR for ekinhbayar/gitamp';
        $this->assertEvent['sound']['type']          = 'SwellEgg';

        $event = new PullRequestEvent($this->event);

        $this->assertSame($this->assertEvent, $event->getAsArray());
    }
}
