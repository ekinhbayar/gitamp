<?php declare(strict_types=1);

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
    private $connections;
    private $ips;
    private $counter;
    private $origins;
    private $gitamp;

    public function __construct(Counter $counter, array $origins, GitAmp $gitamp)
    {
        $this->origins = $origins;
        $this->counter = $counter;
        $this->gitamp  = $gitamp;
    }

    public function onStart(Endpoint $endpoint)
    {
        $this->endpoint = $endpoint;
        $this->connections = [];
        $this->ips = [];

        $this->counter->set("connected_users", 0);

        repeat(function () {
            $this->emit(yield $this->gitamp->listen());
        }, 25000);
    }

    public function onHandshake(Request $request, Response $response)
    {
        $origin = $request->getHeader("origin");

        if (!in_array($origin, $this->origins, true)) {
            $response->setStatus(403);
            $response->end("<h1>origin not allowed</h1>");
            return null;
        }

        return $request->getConnectionInfo()["client_addr"];
    }

    public function onOpen(int $clientId, $handshakeData) {
        // We keep one map for all connected clients.
        $this->connections[$clientId] = $handshakeData;
        // And another one for multiple clients with the same IP.
        $this->ips[$handshakeData][$clientId] = true;

        // send initial results
        $this->emit(yield $this->gitamp->listen());

        yield $this->counter->increment("connected_users");
        $this->sendConnectedUsersCount(yield $this->counter->get("connected_users"));
    }

    /**
     * @param Results $events
     */
    public function emit(Results $events) {
        if (!$events->hasEvents()) return;

        $this->endpoint->send(null, $events->jsonEncode());
    }

    public function sendConnectedUsersCount(int $count) {
        $this->endpoint->send(null, json_encode(['connectedUsers' => $count]));
    }

    public function onData(int $clientId, Websocket\Message $msg) {
        // yielding $msg buffers the complete payload into a single string.
    }

    public function onClose(int $clientId, int $code, string $reason) {
        // Always clean up data when clients disconnect, otherwise we'll leak memory.
        $ip = $this->connections[$clientId];
        unset($this->connections[$clientId]);
        unset($this->ips[$ip][$clientId]);
        if (empty($this->ips[$ip])) {
            unset($this->ips[$ip]);
        }

        yield $this->counter->decrement("connected_users");
        $this->sendConnectedUsersCount(yield $this->counter->get("connected_users"));
    }

    public function onStop() {
        // intentionally left blank
    }

}
