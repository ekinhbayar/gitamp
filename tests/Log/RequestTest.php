<?php declare(strict_types = 1);

namespace ekinhbayar\GitAmpTests\Log;

use Aerys\Body;
use Aerys\Request;
use Aerys\Response;
use Aerys\Server;
use ekinhbayar\GitAmp\Log\Request as RequestLogger;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class RequestTest extends TestCase
{
    public function testInvoke()
    {
        $body = $this->createMock(Body::class);

        $logger = $this->createMock(LoggerInterface::class);

        $logger
            ->expects($this->once())
            ->method('debug')
            ->with('Incoming request', [
                'method'     => 'GET',
                'uri'        => '/ws',
                'headers'    => ['foo' => 'bar'],
                'parameters' => ['baz' => 'qux'],
                'body'       => $body,
            ])
        ;

        $requestLogger = new RequestLogger();

        $requestLogger->boot($this->createMock(Server::class), $logger);

        $request = $this->createMock(Request::class);

        $request
            ->expects($this->once())
            ->method('getMethod')
            ->will($this->returnValue('GET'))
        ;

        $request
            ->expects($this->once())
            ->method('getUri')
            ->will($this->returnValue('/ws'))
        ;

        $request
            ->expects($this->once())
            ->method('getAllHeaders')
            ->will($this->returnValue(['foo' => 'bar']))
        ;

        $request
            ->expects($this->once())
            ->method('getAllParams')
            ->will($this->returnValue(['baz' => 'qux']))
        ;

        $request
            ->expects($this->once())
            ->method('getBody')
            ->will($this->returnValue($body))
        ;

        $requestLogger($request, $this->createMock(Response::class));
    }
}
