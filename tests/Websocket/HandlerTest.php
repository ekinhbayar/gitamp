<?php declare(strict_types = 1);

namespace ekinhbayar\GitAmpTests\Websocket;

use Amp\Delayed;
use Amp\Http\Server\DefaultErrorHandler;
use Amp\Http\Server\Driver\Client;
use Amp\Http\Server\HttpServer;
use Amp\Http\Server\Request;
use Amp\Http\Server\RequestHandler\CallableRequestHandler;
use Amp\Http\Server\Response;
use Amp\Http\Status;
use Amp\Loop;
use Amp\Socket\Server;
use Amp\Success;
use Amp\Websocket\Client as WebsocketClient;
use Amp\Websocket\Server\Gateway;
use ekinhbayar\GitAmp\Configuration;
use ekinhbayar\GitAmp\Github\Token;
use ekinhbayar\GitAmp\Provider\Listener;
use ekinhbayar\GitAmp\Response\Results;
use ekinhbayar\GitAmp\ServerAddress;
use ekinhbayar\GitAmp\Websocket\Handler;
use League\Uri\Uri;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\UriInterface as PsrUri;
use function Amp\Promise\wait;

class HandlerTest extends TestCase
{
    private Listener $listener;

    private Gateway $gateway;

    private Logger $logger;

    private HttpServer $httpServer;

    private Handler $handler;

    public function setUp(): void
    {
        $this->listener = $this->createMock(Listener::class);

        $configuration = (new Configuration(new Token('12345')))
            ->addWebsocketAddress(Uri::createFromString('https://gitamp.audio'))
            ->bind(new ServerAddress('127.0.0.1', 1337))
        ;
        $results = $this->createMock(Results::class);

        $this->logger  = $this->createMock(Logger::class);
        $this->gateway = $this->createMock(Gateway::class);

        $this->httpServer = new HttpServer(
            [Server::listen("tcp://127.0.0.1:0")],
            new CallableRequestHandler(function () {
                yield new Delayed(1500);

                return new Response(Status::NO_CONTENT);
            }
        ), $this->logger);

        $this->handler = new Handler($this->listener, $configuration, $results, $this->logger);
    }

    public function testHandleHandshakeReturnsForbiddenOnInvalidOrigin(): void
    {
        $request = new Request(
            $this->createMock(Client::class),
            'GET',
            $this->createMock(PsrUri::class),
            ['origin' => 'https://notgitamp.audio'],
        );

        $this->gateway
            ->expects($this->once())
            ->method('getErrorHandler')
            ->willReturn(new DefaultErrorHandler())
        ;

        $this->handler->handleHandshake($this->gateway, $request, new Response());
    }

    public function testHandleHandshakeReturnsSuccessfulResponseWhenOriginsMatch(): void
    {
        $request = new Request(
            $this->createMock(Client::class),
            'GET',
            $this->createMock(PsrUri::class),
            ['origin' => 'https://gitamp.audio'],
        );

        $response = wait($this->handler->handleHandshake($this->gateway, $request, new Response()));

        $this->assertSame(200, $response->getStatus());
    }

    public function testOnStartEmitsWithoutEvents(): void
    {
        $results = $this->createMock(Results::class);

        $results
            ->expects($this->once())
            ->method('hasEvents')
            ->will($this->returnValue(false))
        ;

        $this->listener
            ->expects($this->once())
            ->method('listen')
            ->will($this->returnValue(new Success($results)))
        ;

        Loop::run(function () {
            yield $this->handler->onStart($this->httpServer, $this->gateway);

            Loop::stop();
        });
    }

    public function testOnStartEmitsWithEvents(): void
    {
        $results = $this->createMock(Results::class);

        $results
            ->expects($this->once())
            ->method('hasEvents')
            ->will($this->returnValue(true))
        ;

        $this->listener
            ->expects($this->once())
            ->method('listen')
            ->will($this->returnValue(new Success($results)))
        ;

        $this->gateway
            ->expects($this->once())
            ->method('broadcast')
        ;

        Loop::run(function () {
            yield $this->handler->onStart($this->httpServer, $this->gateway);

            Loop::stop();
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

        $this->listener
            ->expects($this->once())
            ->method('listen')
            ->willReturn(new Success($results))
        ;

        $this->gateway
            ->expects($this->once())
            ->method('broadcast')
        ;

        $websocketClient = $this->createMock(WebsocketClient::class);

        $websocketClient
            ->expects($this->never())
            ->method('send')
        ;

        $request = new Request(
            $this->createMock(Client::class),
            'GET',
            $this->createMock(PsrUri::class),
            ['origin' => 'https://gitamp.audio'],
        );

        Loop::run(function () use ($websocketClient, $request) {
            Loop::defer(function () {
                Loop::stop();
            });

            yield $this->handler->onStart($this->httpServer, $this->gateway);

            yield $this->handler->handleClient($this->gateway, $websocketClient, $request, new Response());
        });
    }

    public function testHandleClientWithExistingEvents(): void
    {
        $results = $this->createMock(Results::class);

        $results
            ->expects($this->once())
            ->method('hasEvents')
            ->willReturn(true)
        ;

        $results
            ->expects($this->once())
            ->method('jsonEncode')
            ->willReturn('{}')
        ;

        $this->listener
            ->expects($this->once())
            ->method('listen')
            ->willReturn(new Success($results))
        ;

        $this->gateway
            ->expects($this->exactly(2))
            ->method('broadcast')
        ;

        $websocketClient = $this->createMock(WebsocketClient::class);

        $request = new Request(
            $this->createMock(Client::class),
            'GET',
            $this->createMock(PsrUri::class),
            ['origin' => 'https://gitamp.audio'],
        );

        Loop::run(function () use ($websocketClient, $request) {
            Loop::defer(function () {
                Loop::stop();
            });

            yield $this->handler->onStart($this->httpServer, $this->gateway);

            yield $this->handler->handleClient($this->gateway, $websocketClient, $request, new Response());
        });
    }

    public function testOnStopReturnsNothing(): void
    {
        $this->assertNull(wait($this->handler->onStop($this->httpServer, $this->gateway)));
    }
}
