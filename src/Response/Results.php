<?php declare(strict_types = 1);

namespace ekinhbayar\GitAmp\Response;

use Amp\Artax\Response;
use ekinhbayar\GitAmp\Events\Factory as EventFactory;
use ekinhbayar\GitAmp\Events\UnknownEventException;
use ExceptionalJSON\DecodeErrorException;
use Psr\Log\LoggerInterface;

class Results
{
    private $eventFactory;

    private $logger;

    private $events = [];

    public function __construct(EventFactory $eventFactory, LoggerInterface $logger)
    {
        $this->eventFactory = $eventFactory;
        $this->logger       = $logger;
    }

    public function appendResponse(Response $response)
    {
        try {
            $events = json_try_decode($response->getBody(), true);
        } catch (DecodeErrorException $e) {
            $this->logger->emergency('Failed to decode response body as JSON', ['exception' => $e]);

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
            $this->logger->debug('Unknown event encountered', ['exception' => $e]);
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
