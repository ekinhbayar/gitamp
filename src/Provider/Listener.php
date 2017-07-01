<?php declare(strict_types = 1);

namespace ekinhbayar\GitAmp\Provider;

use Amp\Promise;

interface Listener
{
    public function listen(): Promise;
}
