<?php declare(strict_types=1);

namespace ekinhbayar\GitAmp\Events;

interface Event
{
    public function getAsArray(): array;
}
