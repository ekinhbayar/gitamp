<?php declare(strict_types = 1);

namespace ekinhbayar\GitAmp\Storage;

interface Counter
{
    public function increment();

    public function decrement();

    public function get(): int;

    public function set(int $val);
}
