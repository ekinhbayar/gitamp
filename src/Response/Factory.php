<?php declare(strict_types = 1);

namespace ekinhbayar\GitAmp\Response;

use Amp\Promise;
use ekinhbayar\GitAmp\Events\Factory as EventFactory;
use Amp\Artax\Response;
use Psr\Log\LoggerInterface;

class Factory
{
    private $eventFactory;

    private $logger;

    public function __construct(EventFactory $eventFactory, LoggerInterface $logger)
    {
        $this->eventFactory = $eventFactory;
        $this->logger       = $logger;
    }

    public function build(Response $response): Promise
    {
        return \Amp\call(function() use ($response) {
            $results = new Results($this->eventFactory, $this->logger);

            yield from $results->appendResponse($response);

            return $results;
        });
    }
}
