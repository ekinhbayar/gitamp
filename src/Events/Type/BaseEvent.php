<?php declare(strict_types=1);

namespace ekinhbayar\GitAmp\Events\Type;

use ekinhbayar\GitAmp\Events\Event;

abstract class BaseEvent implements Event
{
    protected $id;

    protected $type;

    protected $action;

    protected $repository;

    protected $actorName;

    protected $eventUrl;

    protected $message;

    public function __construct(int $id, string $type, string $action, string $repository, string $actorName, string $eventUrl, string $message)
    {
        $this->id         = $id;
        $this->type       = $type;
        $this->action     = $action;
        $this->repository = $repository;
        $this->actorName  = $actorName;
        $this->eventUrl   = $eventUrl;
        $this->message    = $message;
    }
}
