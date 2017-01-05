<?php declare(strict_types=1);

use Aerys\Host;
use Amp\Redis\Client;
use Auryn\Injector;
use ekinhbayar\GitAmp\Github\Credentials;
use ekinhbayar\GitAmp\Storage\Counter;
use ekinhbayar\GitAmp\Storage\RedisCounter;
use ekinhbayar\GitAmp\Websocket\Handler;
use function Aerys\root;
use function Aerys\router;
use function Aerys\websocket;

$configuration = require_once __DIR__ . '/config.php';

$injector = new Injector;

$injector->alias(Counter::class, RedisCounter::class);

$injector->alias(Credentials::class, get_class($configuration['github']));

$injector->share($configuration['github']);

$injector->define(Handler::class, [
    ":origins" => [
        "http://" . $configuration['origins']['websocket'],
        "http://" . $configuration['origins']['server'] ,
    ],
]);

$injector->define(Client::class, [
    ":uri" => "tcp://" . $configuration['redis']['hostname'] . ":" . $configuration['redis']['port']
]);

$websocket = $injector->make(Handler::class);

$router = router()->get("/ws", websocket($websocket));

(new Host)
    ->name($configuration['origins']['server'])
    ->expose($configuration['expose']['ip'], $configuration['expose']['port'])
    ->use($router)
    ->use(root(__DIR__ . "/public"));
