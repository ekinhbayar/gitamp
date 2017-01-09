<?php declare(strict_types = 1);

namespace ekinhbayar\GitAmp\Response;

use Amp\Artax\Response;
use ekinhbayar\GitAmp\Events\Factory;
use ekinhbayar\GitAmp\Events\UnknownEventException;
use ExceptionalJSON\DecodeErrorException;

class Results
{
    private $eventFactory;

    private $events = [];

    public function __construct(Factory $eventFactory)
    {
        $this->eventFactory = $eventFactory;
    }

    public function appendResponse(Response $response)
    {
        try {
            $events = json_try_decode($response->getBody(), true);
        } catch (DecodeErrorException $e) {
            throw new DecodingFailedException('Failed to decode response body as JSON', $e->getCode(), $e);
        }

        foreach ($events as $event) {
            $this->appendEvent($event);
        }
    }

    private function appendEvent(array $event)
    {
        try {
            $this->events[] = $this->eventFactory->build($event);
        } catch (UnknownEventException $e) {
            // maybe log unknown events?
        }
    }

    public function hasEvents(): bool
    {
        return (bool) count($this->events);
    }

    public function jsonEncode(): string
    {
        $events = [];

        foreach ($this->events as $event) {
            $events[] = $event->getAsArray();
        }

        return json_encode($events);
    }
}
