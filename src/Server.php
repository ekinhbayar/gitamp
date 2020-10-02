<?php declare(strict_types=1);

namespace ekinhbayar\GitAmp;

use Amp\Http\Client\HttpClientBuilder;
use Amp\Http\Server\HttpServer;
use Amp\Http\Server\Router;
use Amp\Http\Server\StaticContent\DocumentRoot;
use Amp\Promise;
use Amp\Socket\Server as SocketServer;
use Amp\Websocket\Server\Websocket;
use ekinhbayar\GitAmp\Event\Factory as EventFactory;
use ekinhbayar\GitAmp\Provider\GitHub;
use ekinhbayar\GitAmp\Response\Factory as EventCollectionFactory;
use ekinhbayar\GitAmp\Websocket\Handler;
use Psr\Log\LoggerInterface;

final class Server
{
    private LoggerInterface $logger;

    private Configuration $configuration;

    public function __construct(LoggerInterface $logger, Configuration $configuration)
    {
        $this->logger        = $logger;
        $this->configuration = $configuration;
    }

    public function start(): Promise
    {
        $server = new HttpServer($this->getSockets(), $this->getRouter(), $this->logger);

        return $server->start();
    }

    /**
     * @return array<SocketServer>
     */
    private function getSockets(): array
    {
        return array_map(
            fn (ServerAddress $address) => SocketServer::listen($address->getUri()),
            $this->configuration->getServerAddresses(),
        );
    }

    private function getRouter(): Router
    {
        $router = new Router();

        $router->addRoute('GET', '/ws', $this->getWebSocket());
        $router->setFallback(new DocumentRoot(__DIR__ . '/../public'));

        return $router;
    }

    private function getWebSocket(): Websocket
    {
        $gitHubListener = new GitHub(
            HttpClientBuilder::buildDefault(),
            $this->configuration->getGithubToken(),
            new EventCollectionFactory(new EventFactory($this->configuration->getSpecialRepositories()), $this->logger),
            $this->logger,
        );

        $clientHandler = new Handler($gitHubListener, $this->configuration, $this->logger);

        return new Websocket($clientHandler);
    }
}
