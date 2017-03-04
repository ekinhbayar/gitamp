<?php declare(strict_types = 1);

namespace ekinhbayar\GitAmpTests\Websocket;

use Aerys\Request;
use Aerys\Response;
use ekinhbayar\GitAmp\Client\GitAmp;
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
        $this->origin  = 'https://gitamp.audio';
        $this->gitamp  = $this->createMock(GitAmp::class);
    }

    public function testOnHandshakeReturnsForbiddenOnInvalidOrigin()
    {
        $handler = new Handler($this->counter, $this->origin, $this->gitamp);

        $request  = $this->createMock(Request::class);
        $response = $this->createMock(Response::class);

        $request
            ->expects($this->once())
            ->method('getHeader')
            // this is testing the actual implementation which is actually not needed
            ->with('origin')
            ->will($this->returnValue('https://notgitamp.audio'))
        ;

        $response
            ->expects($this->once())
            ->method('setStatus')
            ->with(403)
        ;

        $response
            ->expects($this->once())
            ->method('end')
            ->with('<h1>origin not allowed</h1>')
        ;

        $this->assertNull($handler->onHandshake($request, $response));
    }

    public function testOnHandshakeReturnsClientAddress()
    {
        $handler = new Handler($this->counter, $this->origin, $this->gitamp);

        $request  = $this->createMock(Request::class);
        $response = $this->createMock(Response::class);

        $request
            ->expects($this->once())
            ->method('getHeader')
            // this is testing the actual implementation which is actually not needed
            ->with('origin')
            ->will($this->returnValue($this->origin))
        ;

        $request
            ->expects($this->once())
            ->method('getConnectionInfo')
            ->will($this->returnValue(['client_addr' => '127.0.0.1']))
        ;

        $this->assertSame('127.0.0.1', $handler->onHandshake($request, $response));
    }
}
