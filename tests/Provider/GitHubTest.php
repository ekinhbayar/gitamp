<?php declare(strict_types=1);

namespace ekinhbayar\GitAmpTests\Provider;

use Amp\ByteStream\Payload;
use Amp\Http\Client\HttpClient;
use Amp\Http\Client\HttpClientBuilder;
use Amp\Http\Client\Response;
use Amp\ByteStream\InMemoryStream;
use Amp\Promise;
use Amp\Success;
use ekinhbayar\GitAmp\Provider\RequestFailedException;
use ekinhbayar\GitAmp\Github\Token;
use ekinhbayar\GitAmp\Provider\GitHub;
use ekinhbayar\GitAmp\Response\Factory;
use ekinhbayar\GitAmp\Response\Results;
use ekinhbayar\GitAmpTests\Fakes\HttpClient\MockSuccessfulResponseInterceptor;
use ekinhbayar\GitAmpTests\Fakes\HttpClient\MockFailedResponseInterceptor;
use ekinhbayar\GitAmpTests\Fakes\HttpClient\MockThrowingResponseInterceptor;
use PHPUnit\Framework\TestCase;
use function Amp\Promise\wait;
use Psr\Log\LoggerInterface;

class GitHubTest extends TestCase
{
    private Token $credentials;

    private $factory;

    private $logger;

    public function setUp(): void
    {
        $this->credentials = new Token('token');
        $this->factory     = $this->createMock(Factory::class);
        $this->logger      = $this->createMock(LoggerInterface::class);
    }

    public function testListenThrowsOnFailedRequest(): void
    {
        $httpClient = (new HttpClientBuilder())
            ->intercept(
                new MockThrowingResponseInterceptor(file_get_contents(TEST_DATA_DIR . '/invalid.json')),
            )->build()
        ;

        $gitamp = new GitHub($httpClient, $this->credentials, $this->factory, $this->logger);

        $this->expectException(RequestFailedException::class);
        $this->expectExceptionMessage('Failed to send GET request to API endpoint');

        wait($gitamp->listen());
    }

    public function testListenThrowsOnNonOkResponse(): void
    {
        $httpClient = (new HttpClientBuilder())
            ->intercept(
                new MockFailedResponseInterceptor(file_get_contents(TEST_DATA_DIR . '/invalid.json')),
            )->build()
        ;

        $gitamp = new GitHub($httpClient, $this->credentials, $this->factory, $this->logger);

        $this->expectException(RequestFailedException::class);
        $this->expectExceptionMessage('A non-200 response status (403 - Forbidden Origin) was encountered');

        wait($gitamp->listen());
    }

    public function testListenReturnsPromise(): void
    {
        $httpClient = (new HttpClientBuilder())
            ->intercept(
                new MockFailedResponseInterceptor(file_get_contents(TEST_DATA_DIR . '/valid.json')),
            )->build()
        ;

        $this->assertInstanceOf(
            Promise::class,
            (new GitHub($httpClient, $this->credentials, $this->factory, $this->logger))->listen()
        );
    }

    public function testListenReturnsResults(): void
    {
        $httpClient = (new HttpClientBuilder())
            ->intercept(
                new MockSuccessfulResponseInterceptor(file_get_contents(TEST_DATA_DIR . '/valid.json')),
            )->build()
        ;

        $this->factory
            ->expects($this->once())
            ->method('build')
            ->will($this->returnValue(new Success($this->createMock(Results::class))))
        ;

        $this->assertInstanceOf(
            Results::class,
            wait((new GitHub($httpClient, $this->credentials, $this->factory, $this->logger))->listen())
        );
    }
}

