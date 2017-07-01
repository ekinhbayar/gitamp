<?php declare(strict_types = 1);

namespace ekinhbayar\GitAmp\Events\Type;

use ekinhbayar\GitAmp\Events\Event;
use ekinhbayar\GitAmp\Presentation\Type;
use ekinhbayar\GitAmp\Presentation\Ring;
use ekinhbayar\GitAmp\Presentation\Sound\BaseSound;

class BaseEvent implements Event
{
    protected $id;

    protected $type;

    protected $url;

    protected $payload;

    protected $message;

    protected $ring;

    protected $sound;

    public function __construct(
        int $id,
        Type $type,
        string $url,
        string $payload,
        string $message,
        Ring $ring,
        BaseSound $sound
    ) {
        $this->id      = $id;
        $this->type    = $type;
        $this->url     = $url;
        $this->payload = $payload;
        $this->message = $message;
        $this->ring    = $ring;
        $this->sound   = $sound;
    }

    public function getAsArray(): array
    {
        return [
            'id'      => $this->id,
            'type'    => $this->type->getValue(),
            'url'     => $this->url,
            'payload' => $this->payload,
            'message' => \ucfirst($this->message),
            'ring'    => $this->ring->getAsArray(),
            'sound'   => $this->sound->getAsArray(),
        ];
    }
}
