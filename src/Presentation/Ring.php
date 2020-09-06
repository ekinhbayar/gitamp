<?php declare(strict_types=1);

namespace ekinhbayar\GitAmp\Presentation;

class Ring
{
    private int $animationDuration;

    private int $radius;

    public function __construct(int $animationDuration, int $radius)
    {
        $this->animationDuration = $animationDuration;
        $this->radius            = $radius;
    }

    public function getAsArray(): array
    {
        return [
            'animationDuration' => $this->animationDuration,
            'radius'            => $this->radius,
        ];
    }
}
