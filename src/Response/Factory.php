<?php declare(strict_types=1);

namespace ekinhbayar\GitAmp\Response;

use Amp\Promise;
use ekinhbayar\GitAmp\Event\Factory as EventFactory;
use Amp\Http\Client\Response;
use Psr\Log\LoggerInterface;
use function Amp\call;

class Factory
{
    private EventFactory $eventFactory;

    private LoggerInterface $logger;

    public function __construct(EventFactory $eventFactory, LoggerInterface $logger)
    {
        $this->eventFactory = $eventFactory;
        $this->logger       = $logger;
    }

    public function build(): Results
    {
        return new Results($this->eventFactory, $this->logger);
    }

    public function buildFromResponse(string $eventNamespace, Response $response): Promise
    {
        return call(function() use ($eventNamespace, $response) {
            $results = $this->build();

            yield $results->appendResponse($eventNamespace, $response);

            return $results;
        });
    }
}
