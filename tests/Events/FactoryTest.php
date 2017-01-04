<?php declare(strict_types=1);

namespace ekinhbayar\GitAmpTests\Events;

use ekinhbayar\GitAmp\Events\Factory;
use ekinhbayar\GitAmp\Events\Type\CreateEvent;
use ekinhbayar\GitAmp\Events\UnknownEventException;
use PHPUnit\Framework\TestCase;

class FactoryTest extends TestCase
{
    public function testBuildThrowsOnUnknownEvent()
    {
        $this->expectException(UnknownEventException::class);

        (new Factory())->build(['type' => 'UnknownEvent']);
    }

    public function testBuildReturnsEvent()
    {
        $event = [
            'id' => '5103197839',
            'type' => 'CreateEvent',
            'repo' => ['name' => 'ekinhbayar/gitamp'],
            'actor' => ['login' => 'PeeHaa'],
        ];

        $this->assertInstanceOf(CreateEvent::class, (new Factory())->build($event));
    }
}
