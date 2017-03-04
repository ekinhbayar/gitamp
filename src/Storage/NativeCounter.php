<?php declare(strict_types = 1);

namespace ekinhbayar\GitAmp\Storage;

class NativeCounter implements Counter
{
    private $counter = 0;

    public function increment()
    {
        $this->counter++;
    }

    public function decrement()
    {
        $this->counter--;
    }

    public function get(): int
    {
        return $this->counter;
    }

    public function set(int $val)
    {
        $this->counter = $val;
    }
}
