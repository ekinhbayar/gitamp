<?php declare(strict_types = 1);

namespace ekinhbayar\GitAmp\Events\Type;

use ekinhbayar\GitAmp\Events\Event;
use ekinhbayar\GitAmp\Presentation\NumericalType;
use ekinhbayar\GitAmp\Presentation\Ring;
use ekinhbayar\GitAmp\Presentation\Sound\BaseSound;

class BaseEvent implements Event
{
    protected $id;

    protected $numericalType;

    protected $type;

    protected $repository;

    protected $url;

    protected $payload;

    protected $message;

    protected $ring;

    protected $sound;

    public function __construct(
        int $id,
        NumericalType $numericalType,
        string $type,
        string $repository,
        string $url,
        string $payload,
        string $message,
        Ring $ring,
        BaseSound $sound
    ) {
        $this->id            = $id;
        $this->numericalType = $numericalType;
        $this->type          = $type;
        $this->repository    = $repository;
        $this->url           = $url;
        $this->payload       = $payload;
        $this->message       = $message;
        $this->ring          = $ring;
        $this->sound         = $sound;
    }

    public function getAsArray(): array
    {
        return [
            'id'            => $this->id,
            'numericalType' => $this->numericalType->getValue(),
            'type'          => $this->type,
            'repoName'      => $this->repository,
            'url'           => $this->url,
            'payload'       => $this->payload,
            'message'       => \ucfirst($this->message),
            'ring'          => $this->ring->getAsArray(),
            'sound'         => $this->sound->getAsArray(),
        ];
    }
}
