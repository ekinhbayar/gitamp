<?php declare(strict_types=1);

namespace ekinhbayar\GitAmp\Websocket;

use Aerys\Request;
use Aerys\Response;
use Aerys\Websocket;
use Aerys\Websocket\Endpoint;
use ekinhbayar\GitAmp\Client\GitAmp;
use ekinhbayar\GitAmp\Storage\Counter;
use function Amp\repeat;

class Handler implements Websocket
{
    private $origins;
    private $endpoint;
    private $connections;
    private $ips;
    private $counter;
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

        yield $this->counter->increment("connected_users");

        $this->emit(yield from $this->gitamp->listen());

        repeat(function() {
            $this->emit(yield from $this->gitamp->listen());
        }, 50000);

    }

    /**
     * todo: refactor, properly handle this, also send connected_users count somehow w/ data
     *
     * @param array $events
     */
    public function emit(array $events) {
        if (!$events) return;

        $this->endpoint->send(null, json_encode($events));
    }

    public function onData(int $clientId, Websocket\Message $msg) {
        // yielding $msg buffers the complete payload into a single string.
        // $ip = $this->connections[$clientId];
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
    }

    public function onStop() {
        // intentionally left blank
    }

}