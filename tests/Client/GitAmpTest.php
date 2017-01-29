<?php declare(strict_types = 1);

namespace ekinhbayar\GitAmpTests\Client;

use Amp\Artax\Client;
use Amp\Artax\ClientException;
use Amp\Artax\Response;
use Amp\Promise;
use Amp\Success;
use ekinhbayar\GitAmp\Client\RequestFailedException;
use ekinhbayar\GitAmp\Github\Token;
use ekinhbayar\GitAmp\Client\GitAmp;
use ekinhbayar\GitAmp\Response\Factory;
use ekinhbayar\GitAmp\Response\Results;
use PHPUnit\Framework\TestCase;
use function Amp\wait;
use Psr\Log\LoggerInterface;

class GitAmpTest extends TestCase
{
    private $credentials;

    private $factory;

    private $logger;

    public function setUp()
    {
        $this->credentials = new Token('token');
        $this->factory     = $this->createMock(Factory::class);
        $this->logger      = $this->createMock(LoggerInterface::class);
    }

    public function testListenThrowsOnFailedRequest()
    {
        $httpClient = $this->createMock(Client::class);

        $httpClient
            ->expects($this->once())
            ->method('request')
            ->will($this->throwException(new ClientException()))
        ;

        $gitamp = new GitAmp($httpClient, $this->credentials, $this->factory, $this->logger);

        $this->expectException(RequestFailedException::class);
        $this->expectExceptionMessage('Failed to send GET request to API endpoint');

        wait($gitamp->listen());
    }

    public function testListenThrowsOnNonOkResponse()
    {
        $response = $this->createMock(Response::class);

        $response
            ->expects($this->exactly(2))
            ->method('getStatus')
            ->will($this->returnValue(403))
        ;

        $response
            ->expects($this->once())
            ->method('getReason')
            ->will($this->returnValue('invalid'))
        ;

        $httpClient = $this->createMock(Client::class);

        $httpClient
            ->expects($this->once())
            ->method('request')
            ->will($this->returnValue(new Success($response)))
        ;

        $gitamp = new GitAmp($httpClient, $this->credentials, $this->factory, $this->logger);

        $this->expectException(RequestFailedException::class);
        $this->expectExceptionMessage('A non-200 response status (403 - invalid) was encountered');

        wait($gitamp->listen());
    }

    public function testListenReturnsPromise()
    {
        $response = $this->createMock(Response::class);

        $response
            ->expects($this->once())
            ->method('getStatus')
            ->will($this->returnValue(200))
        ;

        $httpClient = $this->createMock(Client::class);

        $httpClient
            ->expects($this->once())
            ->method('request')
            ->will($this->returnValue(new Success($response)))
        ;

        $this->assertInstanceOf(
            Promise::class,
            (new GitAmp($httpClient, $this->credentials, $this->factory, $this->logger))->listen()
        );
    }

    public function testListenReturnsResults()
    {
        $response = $this->createMock(Response::class);

        $response
            ->expects($this->once())
            ->method('getStatus')
            ->will($this->returnValue(200))
        ;

        $httpClient = $this->createMock(Client::class);

        $httpClient
            ->expects($this->once())
            ->method('request')
            ->will($this->returnValue(new Success($response)))
        ;

        $this->assertInstanceOf(
            Results::class,
            wait((new GitAmp($httpClient, $this->credentials, $this->factory, $this->logger))->listen())
        );
    }
}

