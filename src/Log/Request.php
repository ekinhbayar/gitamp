<?php declare(strict_types = 1);

namespace ekinhbayar\GitAmp\Log;

use Aerys\Bootable;
use Aerys\Request as AerysRequest;
use Aerys\Response;use Aerys\Server;
use Psr\Log\LoggerInterface;

class Request implements Bootable
{
    private $logger;

    public function boot(Server $server, LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function __invoke(AerysRequest $request, Response $response)
    {
        $this->logger->debug('Incoming request', [
            'method'     => $request->getMethod(),
            'uri'        => $request->getUri(),
            'headers'    => $request->getAllHeaders(),
            'parameters' => $request->getAllParams(),
            'body'       => $request->getBody(),
        ]);
    }
}
