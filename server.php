<?php declare(strict_types=1);

use Amp\Loop;
use Amp\Promise;
use Amp\Websocket\Server\Websocket;
use Amp\Http\Client\HttpClientBuilder;
use Amp\Http\Server\HttpServer;
use Amp\Http\Server\Router;
use Amp\Http\Server\StaticContent\DocumentRoot;
use Amp\Log\ConsoleFormatter;
use Amp\Socket\Server;
use ekinhbayar\GitAmp\Event\Factory as EventFactory;
use ekinhbayar\GitAmp\Http\Origin;
use ekinhbayar\GitAmp\Provider\GitHub;
use ekinhbayar\GitAmp\Response\Factory as EventCollectionFactory;
use ekinhbayar\GitAmp\Websocket\Handler;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use function Amp\ByteStream\getStdout;

require __DIR__ . '/vendor/autoload.php';

$configuration = require_once __DIR__ . '/config.php';

$logHandler = new StreamHandler(getStdout()->getResource(), $configuration['logLevel']);
$logHandler->setFormatter(new ConsoleFormatter());

$logger = new Logger('gitamp');
$logger->pushHandler($logHandler);

$gitHubListener = new GitHub(
    HttpClientBuilder::buildDefault(),
    $configuration['github'],
    new EventCollectionFactory(new EventFactory(), $logger),
    $logger,
);

$clientHandler = new Handler(
    (new Origin($configuration))->get(),
    $gitHubListener,
    $logger,
);

$websocket = new Websocket($clientHandler);

Amp\Loop::setErrorHandler(function (Throwable $e) use ($logger) {
    $logger->emergency('GitAmp blew up', ['exception' => $e]);
});

Loop::run(function () use ($websocket, $configuration, $logger): Promise {
    $sockets = [
        Server::listen(sprintf('%s:%d', $configuration['expose']['ip'], $configuration['expose']['port'])),
    ];

    $router = new Router;
    $router->addRoute('GET', '/ws', $websocket);
    $router->setFallback(new DocumentRoot(__DIR__ . '/public'));

    $server = new HttpServer($sockets, $router, $logger);

    return $server->start();
});
