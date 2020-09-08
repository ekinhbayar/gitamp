<?php declare(strict_types=1);

use Amp\Loop;
use Amp\Promise;
use Amp\Websocket\Server\Websocket;
use Amp\Http\Client\HttpClient;
use Amp\Http\Client\HttpClientBuilder;
use Amp\Http\Server\HttpServer;
use Amp\Http\Server\Router;
use Amp\Http\Server\StaticContent\DocumentRoot;
use Amp\Log\ConsoleFormatter;
use Amp\Socket\Server;
use Auryn\Injector;
use ekinhbayar\GitAmp\Github\Credentials;
use ekinhbayar\GitAmp\Http\Origin;
use ekinhbayar\GitAmp\Provider\GitHub;
use ekinhbayar\GitAmp\Provider\Listener;
use ekinhbayar\GitAmp\Websocket\Handler;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Psr\Log\LoggerInterface;
use function Amp\ByteStream\getStdout;

require __DIR__ . '/vendor/autoload.php';

$configuration = require_once __DIR__ . '/config.php';

$injector = new Injector;

$injector->delegate(HttpClient::class, function () {
    return HttpClientBuilder::buildDefault();
});

$injector->share(HttpClient::class);

$injector->alias(Listener::class, GitHub::class);

$injector->alias(Credentials::class, get_class($configuration['github']));

$injector->share($configuration['github']);

$injector->alias(LoggerInterface::class, Logger::class);
$injector->share(Logger::class);

$injector->delegate(Logger::class, function () use ($configuration) {
    $logHandler = new StreamHandler(getStdout()->getResource(), $configuration['logLevel']);
    $logHandler->setFormatter(new ConsoleFormatter());

    $logger = new Logger('gitamp');
    $logger->pushHandler($logHandler);

    return $logger;
});

$logger = $injector->make(Logger::class);

$injector->define(Handler::class, [
    ':origin' => (new Origin($configuration))->get(),
]);

$clientHandler = $injector->make(Handler::class);

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
