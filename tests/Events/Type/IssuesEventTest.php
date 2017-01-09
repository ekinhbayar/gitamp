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
            'type'      => 'IssuesEvent',
            'action'    => 'The action',
            'repoName'  => 'test/repo',
            'actorName' => 'PeeHaa',
            'eventUrl'  => 'http://example.com',
            'message'   => 'Issue title'
        ];
    }

    public function testGetAsArray()
    {
        $event = new IssuesEvent($this->event);

        $this->assertSame($this->assertEvent, $event->getAsArray());
    }
}
