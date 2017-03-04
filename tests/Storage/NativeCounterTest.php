<?php declare(strict_types = 1);

namespace ekinhbayar\GitAmpTests\Storage;

use ekinhbayar\GitAmp\Storage\NativeCounter;
use PHPUnit\Framework\TestCase;

class NativeCounterTest extends TestCase
{
    public function testGet()
    {
        $nativeCounter = new NativeCounter();

        $this->assertSame(0, $nativeCounter->get());
    }

    public function testSet()
    {
        $nativeCounter = new NativeCounter();

        $nativeCounter->set(5);

        $this->assertSame(5, $nativeCounter->get());
    }

    public function testIncrement()
    {
        $nativeCounter = new NativeCounter();

        $nativeCounter->increment();

        $this->assertSame(1, $nativeCounter->get());

        $nativeCounter->increment();

        $this->assertSame(2, $nativeCounter->get());

        $nativeCounter->increment();

        $this->assertSame(3, $nativeCounter->get());
    }

    public function testDecrement()
    {
        $nativeCounter = new NativeCounter();

        $nativeCounter->set(5);

        $nativeCounter->decrement();

        $this->assertSame(4, $nativeCounter->get());

        $nativeCounter->decrement();

        $this->assertSame(3, $nativeCounter->get());

        $nativeCounter->decrement();

        $this->assertSame(2, $nativeCounter->get());
    }
}
