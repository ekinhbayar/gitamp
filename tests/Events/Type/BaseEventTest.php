<?php declare(strict_types = 1);

namespace ekinhbayar\GitAmpTests\Events\Type;

use ekinhbayar\GitAmp\Events\Type\BaseEvent;
use ekinhbayar\GitAmp\Presentation\Information;
use ekinhbayar\GitAmp\Presentation\Ring;
use ekinhbayar\GitAmp\Presentation\Sound\Swell;
use ekinhbayar\GitAmp\Presentation\Type;
use PHPUnit\Framework\TestCase;

class BaseEventTest extends TestCase
{
    public function testGetAsArray()
    {
        $data = [
            'id'        => 1,
            'type'      => 1,
            'information' => [
                'url' => 'url',
                'payload' => 'payload',
                'message' => 'Message',
            ],
            'ring'  => [
                'animationDuration' => 3000,
                'radius'            => 80,
            ],
            'sound' => [
                'size' => 1.0,
                'type' => 'Swell',
            ],
        ];

        $event = new class(
            $data['id'],
            new Type($data['type']),
            new Information(
                $data['information']['url'],
                $data['information']['payload'],
                $data['information']['message']
            ),
            new Ring(3000, 80),
            new Swell()
        ) extends BaseEvent {};

        $this->assertSame($data, $event->getAsArray());
    }
}
