<?php declare(strict_types=1);

namespace ekinhbayar\GitAmp\Response;

use Amp\Artax\Response;
use ekinhbayar\GitAmp\Events\Factory;
use ekinhbayar\GitAmp\Events\GithubEventType;
use ExceptionalJSON\DecodeErrorException;

class Results
{
    private $githubEventType;

    private $eventFactory;

    private $events = [];

    public function __construct(GithubEventType $githubEventType, Factory $eventFactory)
    {
        $this->githubEventType = $githubEventType;
        $this->eventFactory    = $eventFactory;
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
        if (!$this->githubEventType->isValid($event['type'])) {
            return;
        }

        $this->events[] = $this->eventFactory->build($event);
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
