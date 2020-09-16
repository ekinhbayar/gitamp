<?php declare(strict_types=1);

namespace ekinhbayar\GitAmp\Exception;

class UnknownEvent extends Exception
{
    public function __construct(string $event)
    {
        parent::__construct(sprintf('Unknown event (%s) encountered', $event));
    }
}
