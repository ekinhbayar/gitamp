<?php declare(strict_types = 1);

use Aerys\Host;
use Auryn\Injector;
use Amp\Artax\Client;
use Amp\Artax\BasicClient;
use ekinhbayar\GitAmp\Github\Credentials;
use ekinhbayar\GitAmp\Http\Origin;
use ekinhbayar\GitAmp\Log\Request as RequestLogger;
use ekinhbayar\GitAmp\Storage\Counter;
use ekinhbayar\GitAmp\Storage\NativeCounter;
use ekinhbayar\GitAmp\Websocket\Handler;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Psr\Log\LoggerInterface;
use function Aerys\root;
use function Aerys\router;
use function Aerys\websocket;

$configuration = require_once __DIR__ . '/config.php';

$injector = new Injector;

$injector->alias(Counter::class, NativeCounter::class);

$injector->alias(Credentials::class, get_class($configuration['github']));

$injector->share($configuration['github']);

$injector->alias(LoggerInterface::class, Logger::class);
$injector->share(Logger::class);

$injector->delegate(Logger::class, function() use ($configuration) {
    $logger = new Logger('gitamp');

    $logger->pushHandler(new StreamHandler('php://stdout', $configuration['logLevel']));

    return $logger;
});

$injector->define(Handler::class, [
    ':origin' => (new Origin($configuration))->get(),
]);

$injector->alias(Client::class, BasicClient::class);

$websocket = $injector->make(Handler::class);

$router = router()->get('/ws', websocket($websocket));

$requestLogger = new RequestLogger();

if (isset($configuration['ssl'])) {
    (new Host())
        ->name($configuration['hostname'])
        ->expose($configuration['expose']['ip'], $configuration['expose']['port'])
        ->use($requestLogger)
        ->redirect('https://' . $configuration['hostname'])
    ;

    (new Host())
        ->name($configuration['hostname'])
        ->expose($configuration['ssl']['ip'], $configuration['ssl']['port'])
        ->encrypt($configuration['ssl']['certificate'], $configuration['ssl']['key'])
        ->use($requestLogger)
        ->use($router)
        ->use(root(__DIR__ . '/public'))
    ;
} else {
    (new Host())
        ->name($configuration['hostname'])
        ->expose($configuration['expose']['ip'], $configuration['expose']['port'])
        ->use($requestLogger)
        ->use($router)
        ->use(root(__DIR__ . '/public'))
    ;
}

$logger = $injector->make(LoggerInterface::class);

Amp\Loop::setErrorHandler(function(Throwable $e) use ($logger) {
    $logger->emergency('GitAmp blew up', ['exception' => $e]);
});
