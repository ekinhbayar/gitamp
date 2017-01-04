<?php
use Aerys\Host;
use Amp\Redis\Client;
use Amp\Artax\Client as ArtaxClient;
use Auryn\Injector;
use ekinhbayar\GitAmp\Events\GithubEventType;
use ekinhbayar\GitAmp\Github\Credentials;
use ekinhbayar\GitAmp\Storage\Counter;
use ekinhbayar\GitAmp\Storage\RedisCounter;
use ekinhbayar\GitAmp\Websocket\Handler;
use ekinhbayar\GitAmp\Client\GitAmp;
use function Aerys\root;
use function Aerys\router;
use function Aerys\websocket;

$configuration = require_once __DIR__ . '/config.php';

$injector = new Injector;

$injector->alias(Counter::class, RedisCounter::class);

$injector->alias(Credentials::class, get_class($configuration['github']));

// @todo find out why the credentials are being instantiated multiple times
$injector->share($configuration['github']);

$injector->define(Handler::class, [
    ":origins" => [
        "http://" . $configuration['origins']['websocket']['hostname'] . ":" . $configuration['origins']['websocket']['port'],
        "http://" . $configuration['origins']['server']['hostname'] . ":" . $configuration['origins']['server']['port'],
    ],
    ":audiohub" => new GitAmp(new ArtaxClient(), $configuration['github'], new GithubEventType())
]);

$injector->make(GitAmp::class);

$injector->define(Client::class, [
    ":uri" => "tcp://" . $configuration['redis']['hostname'] . ":" . $configuration['redis']['port']
]);

$websocket = $injector->make(Handler::class);

$router = router()->get("/ws", websocket($websocket));

// add document root
$root = root(__DIR__ . "/public");

(new Host)
    ->name($configuration['origins']['server']['hostname'])
    ->expose("*", $configuration['expose'])
    ->use($router)
    ->use($root);
