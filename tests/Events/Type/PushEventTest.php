<?php declare(strict_types = 1);

namespace ekinhbayar\GitAmpTests\Events\Type;

use ekinhbayar\GitAmp\Events\Type\PushEvent;
use PHPUnit\Framework\TestCase;

class PushEventTest extends TestCase
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
                'commits' => [['message' => 'Commit message']],
            ],
        ];

        $this->assertEvent = [
            'id'        => 1,
            'type'      => 'PushEvent',
            'action'    => 'pushed to',
            'repoName'  => 'test/repo',
            'actorName' => 'PeeHaa',
            'eventUrl'  => 'https://github.com/test/repo',
            'message'   => 'Commit message'
        ];
    }

    public function testGetAsArrayWithCommitMessage()
    {
        $event = new PushEvent($this->event);

        $this->assertSame($this->assertEvent, $event->getAsArray());
    }

    public function testGetAsArrayWithoutCommitMessage()
    {
        unset($this->event['payload']['commits'][0]['message']);

        $this->assertEvent['message'] = 'https://github.com/PeeHaa';

        $event = new PushEvent($this->event);

        $this->assertSame($this->assertEvent, $event->getAsArray());
    }
}
