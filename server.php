<?php declare(strict_types = 1);

use Aerys\Host;
use Amp\Redis\Client;
use Auryn\Injector;
use ekinhbayar\GitAmp\Github\Credentials;
use ekinhbayar\GitAmp\Storage\Counter;
use ekinhbayar\GitAmp\Storage\RedisCounter;
use ekinhbayar\GitAmp\Websocket\Handler;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Psr\Log\LoggerInterface;
use function Aerys\root;
use function Aerys\router;
use function Aerys\websocket;

$configuration = require_once __DIR__ . '/config.php';

$injector = new Injector;

$injector->alias(Counter::class, RedisCounter::class);

$injector->alias(Credentials::class, get_class($configuration['github']));

$injector->share($configuration['github']);

$injector->alias(LoggerInterface::class, Logger::class);
$injector->share(Logger::class);

$injector->delegate(Logger::class, function() {
    $logger = new Logger('gitamp');

    $logger->pushHandler(new StreamHandler('php://stdout', Logger::DEBUG));

    return $logger;
});

// @todo unuglify this
if (isset($configuration['ssl'])) {
    $origin = 'https://' . $configuration['hostname'];

    if ($configuration['ssl']['port'] !== 443) {
        $origin .= ':' . $configuration['ssl']['port'];
    }
} else {
    $origin = 'http://' . $configuration['hostname'];

    if ($configuration['expose']['port'] !== 80) {
        $origin .= ':' . $configuration['expose']['port'];
    }
}

$injector->define(Handler::class, [
    ':origin' => $origin,
]);

$injector->define(Client::class, [
    ':uri' => $configuration['redis']
]);

$websocket = $injector->make(Handler::class);

$router = router()->get("/ws", websocket($websocket));

if (isset($configuration['ssl'])) {
    (new Host())
        ->name($configuration['hostname'])
        ->expose($configuration['expose']['ip'], $configuration['expose']['port'])
        ->redirect('https://' . $configuration['hostname'])
    ;

    (new Host())
        ->name($configuration['hostname'])
        ->expose($configuration['ssl']['ip'], $configuration['ssl']['port'])
        ->encrypt($configuration['ssl']['certificate'], $configuration['ssl']['key'])
        ->use($router)
        ->use(root(__DIR__ . '/public'))
    ;
} else {
    (new Host())
        ->name($configuration['hostname'])
        ->expose($configuration['expose']['ip'], $configuration['expose']['port'])
        ->use($router)
        ->use(root(__DIR__ . '/public'))
    ;
}

$logger = $injector->make(LoggerInterface::class);

\Amp\onError(function(Throwable $e) use ($logger) {
    $logger->emergency('GitAmp blew up', ['exception' => $e]);
});
