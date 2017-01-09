<?php declare(strict_types = 1);

namespace ekinhbayar\GitAmpTests\Events\Type;

use ekinhbayar\GitAmp\Events\Type\ForkEvent;
use PHPUnit\Framework\TestCase;

class ForkEventTest extends TestCase
{
    private $event;

    private $assertEvent;

    public function setUp()
    {
        $this->event = [
            'id'      => 1,
            'repo'    => ['name' => 'test/repo'],
            'actor'   => ['login' => 'PeeHaa'],
        ];

        $this->assertEvent = [
            'id'        => 1,
            'type'      => 'ForkEvent',
            'action'    => 'forked',
            'repoName'  => 'test/repo',
            'actorName' => 'PeeHaa',
            'eventUrl'  => 'https://github.com/test/repo',
            'message'   => 'not sure if stupid but works anyway'
        ];
    }

    public function testGetAsArray()
    {
        $event = new ForkEvent($this->event);

        $this->assertSame($this->assertEvent, $event->getAsArray());
    }
}
