<?php declare(strict_types = 1);

namespace ekinhbayar\GitAmp\Response;

use ekinhbayar\GitAmp\Events\Factory as EventFactory;
use Amp\Artax\Response;

class Factory
{
    private $eventFactory;

    public function __construct(EventFactory $eventFactory)
    {
        $this->eventFactory = $eventFactory;
    }

    public function build(Response $response): Results
    {
        $results = new Results($this->eventFactory);

        $results->appendResponse($response);

        return $results;
    }
}
