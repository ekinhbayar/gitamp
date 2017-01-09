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
            'type'      => 'PullRequestEvent',
            'action'    => 'The action',
            'repoName'  => 'test/repo',
            'actorName' => 'PeeHaa',
            'eventUrl'  => 'http://example.com',
            'message'   => 'PR title'
        ];
    }

    public function testGetAsArray()
    {
        $event = new PullRequestEvent($this->event);

        $this->assertSame($this->assertEvent, $event->getAsArray());
    }
}
