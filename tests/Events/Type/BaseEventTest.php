<?php declare(strict_types = 1);

namespace ekinhbayar\GitAmpTests\Events\Type;

use ekinhbayar\GitAmp\Events\Type\BaseEvent;
use PHPUnit\Framework\TestCase;

class BaseEventTest extends TestCase
{
    public function testGetAsArray()
    {
        $data = [
            'id'        => 1,
            'type'      => 'TestEvent',
            'action'    => 'Test action',
            'repoName'  => 'test/repo',
            'actorName' => 'TestActor',
            'eventUrl'  => 'http://example.com',
            'message'   => 'Test message.'
        ];

        $event = new class(
            $data['id'],
            $data['type'],
            $data['action'],
            $data['repoName'],
            $data['actorName'],
            $data['eventUrl'],
            $data['message']
        ) extends BaseEvent {};

        $this->assertSame($data, $event->getAsArray());
    }
}
