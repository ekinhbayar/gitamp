<?php
use Aerys\Host;
use Amp\Redis\Client;
use Amp\Artax\Client as ArtaxClient;
use Auryn\Injector;
use ekinhbayar\GitAmp\Github\Credentials;
use ekinhbayar\GitAmp\Github\User;
use ekinhbayar\GitAmp\Github\Token;
use ekinhbayar\GitAmp\Storage\Counter;
use ekinhbayar\GitAmp\Storage\RedisCounter;
use ekinhbayar\GitAmp\Websocket\Handler;
use ekinhbayar\GitAmp\Client\GitAmp;
use function Aerys\root;
use function Aerys\router;
use function Aerys\websocket;

$injector = new Injector;
$injector->alias(Counter::class, RedisCounter::class);

/*
 * todo: make this work and get rid of that line below
 * $injector->define(Credentials::class, [
    ":username" => "YOUR_USERNAME",
    ":password" => "YOUR_PWD",
    ":token"    => "YOUR_TOKEN"
]);
$injector->make(Credentials::class);*/

$credentials = new Token('your-token');
$injector->alias(Credentials::class, Token::class);

//$credentials = new User('your-username', 'your-password');
//$injector->alias(Credentials::class, User::class);

// @todo find out why the credentials are being instantiated multiple times
$injector->share($credentials);

$injector->define(Handler::class, [
    ":origins" => ["http://localhost:1337"],
    ":audiohub" => new GitAmp(new ArtaxClient(), $credentials)
]);

$injector->make(GitAmp::class);

$injector->define(Client::class, [":uri" => "tcp://localhost:6379"]);

$websocket = $injector->make(Handler::class);

$router = router()->get("/ws", websocket($websocket));
// add document root
$root = root(__DIR__ . "/public");
(new Host)
    ->name("localhost")
    ->expose("*", 1337)
    ->use($router)
    ->use($root);