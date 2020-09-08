<?php declare(strict_types=1);

namespace ekinhbayar\GitAmp\Presentation\Sound;

class SwellEgg extends BaseSound
{
    public function getAsArray(): array
    {
        return [
            'size' => $this->size,
            'type' => 'SwellEgg',
        ];
    }
}
