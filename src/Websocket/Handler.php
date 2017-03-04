<?php declare(strict_types = 1);

namespace ekinhbayar\GitAmp\Websocket;

use Aerys\Request;
use Aerys\Response;
use Aerys\Websocket;
use Aerys\Websocket\Endpoint;
use ekinhbayar\GitAmp\Client\GitAmp;
use ekinhbayar\GitAmp\Response\Results;
use ekinhbayar\GitAmp\Storage\Counter;
use function Amp\repeat;

class Handler implements Websocket
{

    private $endpoint;
    private $counter;
    private $origin;
    private $gitamp;

    public function __construct(Counter $counter, string $origin, GitAmp $gitamp)
    {
        $this->origin = $origin;
        $this->counter = $counter;
        $this->gitamp  = $gitamp;
    }

    public function onStart(Endpoint $endpoint)
    {
        $this->endpoint = $endpoint;

        $this->counter->set('connected_users', 0);

        repeat(function() {
            $this->emit(yield $this->gitamp->listen());
        }, 25000);
    }

    public function onHandshake(Request $request, Response $response)
    {
        if ($request->getHeader("origin") !== $this->origin) {
            $response->setStatus(403);
            $response->end("<h1>origin not allowed</h1>");

            return null;
        }

        return $request->getConnectionInfo()['client_addr'];
    }

    public function onOpen(int $clientId, $handshakeData) {
        // send initial results
        $this->emit(yield $this->gitamp->listen());

        yield $this->counter->increment('connected_users');
        $this->sendConnectedUsersCount(yield $this->counter->get('connected_users'));
    }

    /**
     * @param Results $events
     */
    public function emit(Results $events) {
        if (!$events->hasEvents()) {
            return;
        }

        $this->endpoint->send(null, $events->jsonEncode());
    }

    public function sendConnectedUsersCount(int $count) {
        $this->endpoint->send(null, \json_encode(['connectedUsers' => $count]));
    }

    public function onData(int $clientId, Websocket\Message $msg) {
        // yielding $msg buffers the complete payload into a single string.
    }

    public function onClose(int $clientId, int $code, string $reason) {
        yield $this->counter->decrement('connected_users');
        $this->sendConnectedUsersCount(yield $this->counter->get('connected_users'));
    }

    public function onStop() {
        // intentionally left blank
    }
}
