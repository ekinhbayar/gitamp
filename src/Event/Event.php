<?php declare(strict_types=1);

namespace ekinhbayar\GitAmp\Event;

interface Event
{
    public function getAsArray(): array;
}
