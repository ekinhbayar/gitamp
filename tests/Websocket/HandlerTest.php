<?php declare(strict_types = 1);

namespace ekinhbayar\GitAmpTests\Websocket;

use Amp\Delayed;
use Amp\Http\Server\DefaultErrorHandler;
use Amp\Http\Server\Driver\Client;
use Amp\Http\Server\HttpServer;
use Amp\Http\Server\Request;
use Amp\Http\Server\RequestHandler\CallableRequestHandler;
use Amp\Http\Server\Response;
use Amp\Http\Server\Router;
use Amp\Http\Status;
use Amp\Socket\Server;
use Amp\Websocket\Client as WebsocketClient;
use Amp\Websocket\Server\Gateway;
use Amp\Loop;
use Amp\Success;
use Amp\Websocket\Server\Websocket;
use ekinhbayar\GitAmp\Provider\GitHub;
use ekinhbayar\GitAmp\Response\Results;
use ekinhbayar\GitAmp\Websocket\Handler;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\UriInterface as PsrUri;
use function Amp\Promise\wait;

class HandlerTest extends TestCase
{
    private string $origin;

    private $gitamp;

    private $gateway;

    private $logger;

    private HttpServer $httpServer;

    public function setUp(): void
    {
        $this->origin     = 'https://gitamp.audio';
        $this->gitamp     = $this->createMock(GitHub::class);
        $this->gateway    = $this->createMock(Gateway::class);
        $this->logger     = $this->createMock(Logger::class);
        $this->httpServer = new HttpServer(
            [Server::listen("tcp://127.0.0.1:0")],
            new CallableRequestHandler(function () {
                yield new Delayed(1500);

                return new Response(Status::NO_CONTENT);
            }
        ), $this->logger);
    }

    public function testHandleHandshakeReturnsForbiddenOnInvalidOrigin(): void
    {
        $handler = new Handler($this->origin, $this->gitamp, $this->logger);

        $client = $this->createMock(Client::class);

        $uri = $this->createMock(PsrUri::class);

        $request = new Request($client, 'GET', $uri, ['origin' => 'https://notgitamp.audio']);

        $response = new Response(403);

        $this->gateway
            ->expects($this->once())
            ->method('getErrorHandler')
            ->willReturn(new DefaultErrorHandler())
        ;

        $handler->handleHandshake($this->gateway, $request, $response);
    }

    public function testHandleHandshakeReturnsSuccessfulResponseWhenOriginsMatch(): void
    {
        $handler = new Handler($this->origin, $this->gitamp, $this->logger);

        $client = $this->createMock(Client::class);

        $uri = $this->createMock(PsrUri::class);

        $request = new Request($client, 'GET', $uri, ['origin' => $this->origin]);

        $response = new Response(200);

        $result = wait($handler->handleHandshake($this->gateway, $request, $response));

        $this->assertSame($response->getStatus(), $result->getStatus());
    }

    public function testOnStartEmitsWithoutEvents(): void
    {
        $results = $this->createMock(Results::class);

        $results
            ->expects($this->once())
            ->method('hasEvents')
            ->will($this->returnValue(false));

        $this->gitamp
            ->expects($this->once())
            ->method('listen')
            ->will($this->returnValue(new Success($results)));

        $handler = new Handler($this->origin, $this->gitamp, $this->logger);

        Loop::run(function () use ($handler) {
            $handler->onStart($this->httpServer, $this->gateway);

            Loop::stop();
        });
    }

    public function testOnStartEmitsWithEvents(): void
    {
        $results = $this->createMock(Results::class);

        $results
            ->expects($this->exactly(2))
            ->method('hasEvents')
            ->will($this->returnValue(true));

        $this->gitamp
            ->expects($this->exactly(2))
            ->method('listen')
            ->will($this->returnValue(new Success($results)));

        $this->gateway
            ->expects($this->exactly(2))
            ->method('broadcast');

        $handler = new Handler($this->origin, $this->gitamp, $this->logger);

        Loop::run(function () use ($handler) {
            $handler->onStart($this->httpServer, $this->gateway);

            Loop::delay(25000, "Amp\\Loop::stop");
        });
    }

    public function testHandleClientWithoutExistingEvents(): void
    {
        $results = $this->createMock(Results::class);
        $results
            ->expects($this->once())
            ->method('hasEvents')
            ->willReturn(false)
        ;

        $this->gitamp
            ->expects($this->once())
            ->method('listen')
            ->willReturn(new Success($results))
        ;

        $this->gateway
            ->expects($this->once())
            ->method('broadcast')
        ;

        $handler = new Handler($this->origin, $this->gitamp, $this->logger);

        $websocketClient = $this->createMock(WebsocketClient::class);

        $websocketClient
            ->expects($this->never())
            ->method('send')
        ;

        $client = $this->createMock(Client::class);

        $uri = $this->createMock(PsrUri::class);

        $request = new Request($client, 'GET', $uri, ['origin' => $this->origin]);

        $response = new Response(200);

        Loop::run(function () use ($handler, $websocketClient, $request, $response) {
            $handler->onStart($this->httpServer, $this->gateway);

            $handler->handleClient($this->gateway, $websocketClient, $request, $response);

            Loop::stop();
        });
    }

    public function testHandleClientWithExistingEvents(): void
    {
        $this->markTestSkipped('Need to come back to this');

        $results = $this->createMock(Results::class);
        $results
            ->expects($this->exactly(2))
            ->method('hasEvents')
            ->willReturn(true)
        ;

        $results
            ->expects($this->exactly(2))
            ->method('jsonEncode')
            ->willReturn('{}')
        ;

        $this->gitamp
            ->expects($this->exactly(2))
            ->method('listen')
            ->willReturn(new Success($results))
        ;

        $this->gateway
            ->expects($this->exactly(2))
            ->method('broadcast')
        ;

        $handler = new Handler($this->origin, $this->gitamp, $this->logger);

        $websocketClient = $this->createMock(WebsocketClient::class);

        $websocketClient
            ->expects($this->once())
            ->method('send')
        ;

        $client = $this->createMock(Client::class);

        $uri = $this->createMock(PsrUri::class);

        $request = new Request($client, 'GET', $uri, ['origin' => $this->origin]);

        $response = new Response(200);

        Loop::run(function () use ($handler, $websocketClient, $request, $response) {
            $handler->onStart($this->httpServer, $this->gateway);

            yield $handler->handleClient($this->gateway, $websocketClient, $request, $response);

            Loop::stop();
        });
    }

    public function testOnStopReturnsNothing(): void
    {
        $handler = new Handler($this->origin, $this->gitamp, $this->logger);

        $this->assertNull(wait($handler->onStop($this->httpServer, $this->gateway)));
    }
}
