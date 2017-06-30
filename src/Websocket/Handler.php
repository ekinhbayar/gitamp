<?php declare(strict_types = 1);

namespace ekinhbayar\GitAmp\Websocket;

use function Amp\asyncCall;
use Amp\Delayed;
use Aerys\Request;
use Aerys\Response;
use Aerys\Websocket;
use Aerys\Websocket\Endpoint;
use ekinhbayar\GitAmp\Client\GitAmp;
use ekinhbayar\GitAmp\Response\Results;
use ekinhbayar\GitAmp\Storage\Counter;

class Handler implements Websocket
{
    private $endpoint;

    private $counter;

    private $origin;

    private $gitamp;

    public function __construct(Counter $counter, string $origin, GitAmp $gitamp)
    {
        $this->origin  = $origin;
        $this->counter = $counter;
        $this->gitamp  = $gitamp;
    }

    public function onStart(Endpoint $endpoint)
    {
        $this->endpoint = $endpoint;

        $this->counter->set(0);

        asyncCall(function () {
            while (true) {
                $this->emit(yield $this->gitamp->listen());

                yield new Delayed(25000);
            }
        });
    }

    public function onHandshake(Request $request, Response $response)
    {
        if ($request->getHeader('origin') !== $this->origin) {
            $response->setStatus(403);
            $response->end('<h1>origin not allowed</h1>');

            return null;
        }

        return $request->getConnectionInfo()['client_addr'];
    }

    public function onOpen(int $clientId, $handshakeData)
    {
        $this->counter->increment();

        $this->sendConnectedUsersCount($this->counter->get());
    }

    private function emit(Results $events)
    {
        if (!$events->hasEvents()) {
            return;
        }

        $this->endpoint->broadcast($events->jsonEncode());
    }

    private function sendConnectedUsersCount(int $count)
    {
        $this->endpoint->broadcast(\json_encode(['connectedUsers' => $count]));
    }

    public function onData(int $clientId, Websocket\Message $msg)
    {
        // yielding $msg buffers the complete payload into a single string.
    }

    public function onClose(int $clientId, int $code, string $reason)
    {
        $this->counter->decrement();

        $this->sendConnectedUsersCount($this->counter->get());
    }

    public function onStop()
    {
        // intentionally left blank
    }
}
