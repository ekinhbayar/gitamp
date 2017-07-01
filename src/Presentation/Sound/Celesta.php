<?php declare(strict_types = 1);

namespace ekinhbayar\GitAmp\Presentation\Sound;

class Celesta extends BaseSound
{
    public function getAsArray(): array
    {
        return [
            'size' => $this->size,
            'type' => 'Celesta',
        ];
    }
}
