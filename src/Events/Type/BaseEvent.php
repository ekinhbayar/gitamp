<?php declare(strict_types = 1);

namespace ekinhbayar\GitAmp\Events\Type;

use ekinhbayar\GitAmp\Events\Event;
use ekinhbayar\GitAmp\Presentation\NumericalType;
use ekinhbayar\GitAmp\Presentation\Ring;

class BaseEvent implements Event
{
    protected $id;

    private $numericalType;

    protected $type;

    protected $action;

    protected $repository;

    protected $actorName;

    protected $eventUrl;

    protected $message;

    protected $ring;

    public function __construct(
        int $id,
        NumericalType $numericalType,
        string $type,
        string $action,
        string $repository,
        string $actorName,
        string $eventUrl,
        string $message,
        Ring $ring
    ) {
        $this->id            = $id;
        $this->numericalType = $numericalType;
        $this->type          = $type;
        $this->action        = $action;
        $this->repository    = $repository;
        $this->actorName     = $actorName;
        $this->eventUrl      = $eventUrl;
        $this->message       = $message;
        $this->ring          = $ring;
    }

    public function getAsArray(): array
    {
        return [
            'id'            => $this->id,
            'numericalType' => $this->numericalType->getValue(),
            'type'          => $this->type,
            'action'        => $this->action,
            'repoName'      => $this->repository,
            'actorName'     => \ucfirst($this->actorName),
            'eventUrl'      => $this->eventUrl,
            'message'       => $this->message,
            'ring'          => $this->ring->getAsArray(),
        ];
    }
}
