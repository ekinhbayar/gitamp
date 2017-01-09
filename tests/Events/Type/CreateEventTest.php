<?php declare(strict_types = 1);

namespace ekinhbayar\GitAmpTests\Events\Type;

use ekinhbayar\GitAmp\Events\Type\CreateEvent;
use PHPUnit\Framework\TestCase;

class CreateEventTest extends TestCase
{
    private $event;

    private $assertEvent;

    public function setUp()
    {
        $this->event = [
            'id'      => 1,
            'repo'    => ['name' => 'test/repo'],
            'actor'   => ['login' => 'PeeHaa'],
            'payload' => ['description' => 'The description'],
        ];

        $this->assertEvent = [
            'id'        => 1,
            'type'      => 'CreateEvent',
            'action'    => 'created',
            'repoName'  => 'test/repo',
            'actorName' => 'PeeHaa',
            'eventUrl'  => 'https://github.com/test/repo',
            'message'   => 'The description'
        ];
    }

    public function testGetAsArrayWithPayloadDescription()
    {
        $event = new CreateEvent($this->event);

        $this->assertSame($this->assertEvent, $event->getAsArray());
    }

    public function testGetAsArrayWithoutPayloadDescription()
    {
        unset($this->event['payload']['description']);

        $this->assertEvent['message'] = $this->assertEvent['eventUrl'];

        $event = new CreateEvent($this->event);

        $this->assertSame($this->assertEvent, $event->getAsArray());
    }
}
