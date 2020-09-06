<?php declare(strict_types=1);

namespace ekinhbayar\GitAmp\Presentation\Sound;

abstract class BaseSound
{
    protected float $size;

    public function __construct(float $size = 1)
    {
        $this->size = $size;
    }

    abstract public function getAsArray(): array;
}
