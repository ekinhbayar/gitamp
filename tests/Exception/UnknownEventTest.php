<?php declare(strict_types=1);

namespace ekinhbayar\GitAmpTests\Exception;

use ekinhbayar\GitAmp\Exception\UnknownEvent;
use PHPUnit\Framework\TestCase;

class UnknownEventTest extends TestCase
{
    public function testConstructorFormatsMessageCorrectly(): void
    {
        $this->assertSame(
            'Unknown event (EventName) encountered',
            (new UnknownEvent('EventName'))->getMessage(),
        );
    }
}
