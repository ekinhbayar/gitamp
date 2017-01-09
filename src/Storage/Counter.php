<?php declare(strict_types = 1);

namespace ekinhbayar\GitAmp\Storage;

use Amp\Promise;

interface Counter {
    public function increment(string $key): Promise;
    public function decrement(string $key): Promise;
    public function get(string $key): Promise;
}