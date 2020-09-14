<?php declare(strict_types=1);

namespace ekinhbayar\GitAmpTests\Event;

use ekinhbayar\GitAmp\Event\Factory;
use ekinhbayar\GitAmp\Event\GitHub\CreateEvent;
use ekinhbayar\GitAmp\Event\UnknownEventException;
use PHPUnit\Framework\TestCase;

class FactoryTest extends TestCase
{
    public function testBuildThrowsOnUnknownEvent(): void
    {
        $this->expectException(UnknownEventException::class);

        (new Factory([]))->build('Foo', ['type' => 'UnknownEvent']);
    }

    public function testBuildReturnsEvent(): void
    {
        $event = [
            'id'    => '5103197839',
            'type'  => 'CreateEvent',
            'repo'  => ['name' => 'ekinhbayar/gitamp'],
            'actor' => ['login' => 'PeeHaa'],
        ];

        $this->assertInstanceOf(CreateEvent::class, (new Factory([]))->build('ekinhbayar\GitAmp\Event\GitHub', $event));
    }
}
