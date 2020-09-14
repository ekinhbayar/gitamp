<?php declare(strict_types=1);

namespace ekinhbayar\GitAmp\Response;

use Amp\Http\Client\Response;
use Amp\Promise;
use ekinhbayar\GitAmp\Event\Factory as EventFactory;
use ekinhbayar\GitAmp\Exception\DecodingFailed;
use ekinhbayar\GitAmp\Exception\UnknownEvent;
use ExceptionalJSON\DecodeErrorException;
use Psr\Log\LoggerInterface;
use function Amp\call;

class Results
{
    private EventFactory $eventFactory;

    private LoggerInterface $logger;

    private array $events = [];

    public function __construct(EventFactory $eventFactory, LoggerInterface $logger)
    {
        $this->eventFactory = $eventFactory;
        $this->logger       = $logger;
    }

    /**
     * @return Promise<null>
     */
    public function appendResponse(string $eventNamespace, Response $response): Promise
    {
        return call(function () use ($eventNamespace, $response) {
            try {
                $bufferedResponse = yield $response->getBody()->buffer();

                $events = \json_try_decode($bufferedResponse, true);
            } catch (DecodeErrorException $e) {
                $this->logger->emergency('Failed to decode response body as JSON', ['exception' => $e]);

                throw new DecodingFailed('Failed to decode response body as JSON', $e->getCode(), $e);
            }

            foreach ($events as $event) {
                $this->appendEvent($eventNamespace, $event);
            }
        });
    }

    private function appendEvent(string $eventNamespace, array $event): void
    {
        try {
            $this->events[] = $this->eventFactory->build($eventNamespace, $event);
        } catch (UnknownEvent $e) {
            //$this->logger->debug('Unknown event encountered', ['exception' => $e]);
        }
    }

    public function hasEvents(): bool
    {
        return (bool) \count($this->events);
    }

    public function jsonEncode(): string
    {
        $events = [];

        foreach ($this->events as $event) {
            $events[] = $event->getAsArray();
        }

        return \json_encode($events);
    }
}
