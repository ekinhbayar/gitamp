<?php declare(strict_types=1);

namespace ekinhbayar\GitAmp\Websocket;

use Amp\Delayed;
use Amp\Http\Server\HttpServer;
use Amp\Http\Server\Request;
use Amp\Http\Server\Response;
use Amp\Http\Status;
use Amp\Promise;
use Amp\Success;
use Amp\Websocket\Client;
use Amp\Websocket\Server\ClientHandler;
use Amp\Websocket\Server\Gateway;
use Amp\Websocket\Server\WebsocketServerObserver;
use ekinhbayar\GitAmp\Provider\Listener;
use ekinhbayar\GitAmp\Response\Results;
use Psr\Log\LoggerInterface;
use function Amp\asyncCall;
use function Amp\call;

class Handler implements ClientHandler, WebsocketServerObserver
{
    private Gateway $gateway;

    private string $origin;

    private Listener $provider;

    private ?Results $lastEvents = null;

    private LoggerInterface $logger;

    public function __construct(string $origin, Listener $provider, LoggerInterface $logger)
    {
        $this->origin   = $origin;
        $this->provider = $provider;
        $this->logger   = $logger;
    }

    public function handleHandshake(Gateway $gateway, Request $request, Response $response): Promise
    {
        if ($request->getHeader('origin') !== $this->origin) {
            return $gateway->getErrorHandler()->handleError(Status::FORBIDDEN, 'Forbidden Origin', $request);
        }

        return new Success($response);
    }

    public function handleClient(Gateway $gateway, Client $client, Request $request, Response $response): Promise
    {
        $client->onClose(function (Client $client, int $code, string $reason) use ($gateway) {
            $this->logger->info(
                \sprintf(
                    'Client %d disconnected. Code: %d Reason: %s. Total clients: %d',
                    $client->getId(),
                    $code,
                    $reason,
                    count($gateway->getClients()),
                )
            );

            $this->sendConnectedUsersCount(count($gateway->getClients()));
        });

        $this->logger->info(
            \sprintf('Client %d connected. Total clients: %d', $client->getId(), count($gateway->getClients())),
        );

        $this->sendConnectedUsersCount(\count($gateway->getClients()));

        if ($this->lastEvents) {
            $client->send($this->lastEvents->jsonEncode());
        }

        return call(function () use ($gateway, $client): \Generator {
            while ($message = yield $client->receive()) {
                // intentionally keep receiving, otherwise the connection closes instantly for some reason
            }
        });
    }

    private function emit(Results $events): void
    {
        if (!$events->hasEvents()) {
            return;
        }

        $this->lastEvents = $events;

        $this->gateway->broadcast($events->jsonEncode());
    }

    private function sendConnectedUsersCount(int $count): void
    {
        $this->gateway->broadcast(\json_encode(['connectedUsers' => $count]));
    }

    public function onStart(HttpServer $server, Gateway $gateway): Promise
    {
        $this->gateway = $gateway;

        asyncCall(function () {
            while (true) {
                $this->emit(yield $this->provider->listen());

                yield new Delayed(25000);
            }
        });

        return new Success();
    }

    public function onStop(HttpServer $server, Gateway $gateway): Promise
    {
        return new Success();
    }
}
