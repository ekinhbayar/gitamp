<?php declare(strict_types=1);

namespace ekinhbayar\GitAmp\Event;

use ekinhbayar\GitAmp\Presentation\Information;
use ekinhbayar\GitAmp\Presentation\Type;
use ekinhbayar\GitAmp\Presentation\Ring;
use ekinhbayar\GitAmp\Presentation\Sound\BaseSound;

class BaseEvent implements Event
{
    protected int $id;

    protected Type $type;

    protected Information $information;

    protected Ring $ring;

    protected BaseSound $sound;

    public function __construct(int $id, Type $type, Information $information, Ring $ring, BaseSound $sound)
    {
        $this->id          = $id;
        $this->type        = $type;
        $this->information = $information;
        $this->ring        = $ring;
        $this->sound       = $sound;
    }

    public function getAsArray(): array
    {
        return [
            'id'          => $this->id,
            'type'        => $this->type->getValue(),
            'information' => $this->information->getAsArray(),
            'ring'        => $this->ring->getAsArray(),
            'sound'       => $this->sound->getAsArray(),
        ];
    }
}
