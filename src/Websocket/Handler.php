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
use ekinhbayar\GitAmp\Configuration;
use ekinhbayar\GitAmp\Provider\Listener;
use ekinhbayar\GitAmp\Response\Results;
use Psr\Log\LoggerInterface;
use function Amp\asyncCall;
use function Amp\call;

class Handler implements ClientHandler, WebsocketServerObserver
{
    private Gateway $gateway;

    private Listener $provider;

    private Configuration $configuration;

    private Results $lastEvents;

    private LoggerInterface $logger;

    public function __construct(
        Listener $provider,
        Configuration $configuration,
        Results $results,
        LoggerInterface $logger
    ) {
        $this->provider      = $provider;
        $this->configuration = $configuration;
        $this->lastEvents    = $results;
        $this->logger        = $logger;
    }

    public function handleHandshake(Gateway $gateway, Request $request, Response $response): Promise
    {
        if (!$this->configuration->websocketAddressExists($request->getHeader('origin'))) {
            return $gateway->getErrorHandler()->handleError(Status::FORBIDDEN, 'Forbidden Origin', $request);
        }

        return new Success($response);
    }

    public function handleClient(Gateway $gateway, Client $client, Request $request, Response $response): Promise
    {
        return call(function () use ($gateway, $client, $request, $response) {
            $client->onClose(function (Client $client, int $code, string $reason) use ($gateway) {
                yield $this->processDisconnectingClient($gateway, $client, $code, $reason);
            });

            $this->logger->info(
                \sprintf('Client %d connected. Total clients: %d', $client->getId(), count($gateway->getClients())),
            );

            yield $this->sendConnectedUsersCount(\count($gateway->getClients()));

            $client->send($this->lastEvents->jsonEncode());

            yield $client->receive();
        });
    }

    private function processDisconnectingClient(Gateway $gateway, Client  $client, int $code, string $reason): Promise
    {
        return call(function () use ($gateway, $client, $code, $reason) {
            $this->logger->info(
                \sprintf(
                    'Client %d disconnected. Code: %d Reason: %s. Total clients: %d',
                    $client->getId(),
                    $code,
                    $reason,
                    count($gateway->getClients()),
                )
            );

            yield $this->sendConnectedUsersCount(count($gateway->getClients()));
        });
    }

    private function emit(Results $events): Promise
    {
        if (!$events->hasEvents()) {
            return new Success();
        }

        $this->lastEvents = $events;

        return $this->gateway->broadcast($events->jsonEncode());
    }

    private function sendConnectedUsersCount(int $count): Promise
    {
        return $this->gateway->broadcast(\json_encode(['connectedUsers' => $count]));
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
