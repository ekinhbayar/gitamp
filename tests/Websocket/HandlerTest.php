<?php declare(strict_types = 1);

namespace ekinhbayar\GitAmpTests\Websocket;

use Aerys\Request;
use Aerys\Response;
use Aerys\Websocket\Endpoint;
use Aerys\Websocket\Message;
use Amp\Loop;
use Amp\Success;
use ekinhbayar\GitAmp\Client\GitAmp;
use ekinhbayar\GitAmp\Response\Results;
use ekinhbayar\GitAmp\Storage\Counter;
use ekinhbayar\GitAmp\Websocket\Handler;
use PHPUnit\Framework\TestCase;

class HandlerTest extends TestCase
{
    private $counter;

    private $origin;

    private $gitamp;

    public function setUp()
    {
        $this->counter = $this->createMock(Counter::class);
        $this->origin = 'https://gitamp.audio';
        $this->gitamp = $this->createMock(GitAmp::class);
    }

    public function testOnHandshakeReturnsForbiddenOnInvalidOrigin()
    {
        $handler = new Handler($this->counter, $this->origin, $this->gitamp);

        $request = $this->createMock(Request::class);
        $response = $this->createMock(Response::class);

        $request
            ->expects($this->once())
            ->method('getHeader')
            // this is testing the actual implementation which is actually not needed
            ->with('origin')
            ->will($this->returnValue('https://notgitamp.audio'));

        $response
            ->expects($this->once())
            ->method('setStatus')
            ->with(403);

        $response
            ->expects($this->once())
            ->method('end')
            ->with('<h1>origin not allowed</h1>');

        $this->assertNull($handler->onHandshake($request, $response));
    }

    public function testOnHandshakeReturnsClientAddress()
    {
        $handler = new Handler($this->counter, $this->origin, $this->gitamp);

        $request = $this->createMock(Request::class);
        $response = $this->createMock(Response::class);

        $request
            ->expects($this->once())
            ->method('getHeader')
            // this is testing the actual implementation which is actually not needed
            ->with('origin')
            ->will($this->returnValue($this->origin));

        $request
            ->expects($this->once())
            ->method('getConnectionInfo')
            ->will($this->returnValue(['client_addr' => '127.0.0.1']));

        $this->assertSame('127.0.0.1', $handler->onHandshake($request, $response));
    }

    public function testOnOpenResetsConnectedUserCounter()
    {
        $this->counter
            ->expects($this->once())
            ->method('set')
            ->with(0);

        $results = $this->createMock(Results::class);

        $results
            ->expects($this->exactly(2))
            ->method('hasEvents')
            ->will($this->returnValue(false));

        $this->gitamp
            ->expects($this->exactly(2))
            ->method('listen')
            ->will($this->returnValue(new Success($results)));

        $handler = new Handler($this->counter, $this->origin, $this->gitamp);

        $handler->onStart($this->createMock(Endpoint::class));

        Loop::run(function () use ($handler) {
            yield from $handler->onOpen(0, null);

            Loop::repeat(27000, function () { Loop::stop(); });
        });
    }

    /*public function testOnStartEmitsEvents()
    {
        $this->markTestIncomplete('This should be implemented once we have replaced the repeat call.');
    }*/

    public function testOnDataReturnsNothing()
    {
        $handler = new Handler($this->counter, $this->origin, $this->gitamp);

        $this->assertNull($handler->onData(1, $this->createMock(Message::class)));
    }

    public function testOnCloseDecrementsUserCount()
    {
        $this->counter
            ->expects($this->once())
            ->method('decrement');

        $handler = new Handler($this->counter, $this->origin, $this->gitamp);

        Loop::run(function () use ($handler) {
            $handler->onStart($this->createMock(Endpoint::class));

            $handler->onClose(1, 0, 'foo');

            Loop::repeat(2000, function () { Loop::stop(); });
        });
    }

    public function testOnStopReturnsNothing()
    {
        $handler = new Handler($this->counter, $this->origin, $this->gitamp);

        $this->assertNull($handler->onStop());
    }
}
