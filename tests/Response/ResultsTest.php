<?php declare(strict_types=1);

namespace ekinhbayar\GitAmpTests\Response;

use Amp\Http\Client\Request;
use Amp\Http\Client\Response;
use Amp\ByteStream\InputStream;
use Amp\ByteStream\Payload;
use Amp\Loop;
use Amp\Success;
use ekinhbayar\GitAmp\Event\GitHub\PullRequestEvent;
use ekinhbayar\GitAmp\Event\UnknownEventException;
use PHPUnit\Framework\TestCase;
use ekinhbayar\GitAmp\Event\Factory;
use ekinhbayar\GitAmp\Response\Results;
use ekinhbayar\GitAmp\Response\DecodingFailedException;
use Psr\Log\LoggerInterface;

class ResultsTest extends TestCase
{
    private string $eventData;

    private $logger;

    public function setUp(): void
    {
        $this->eventData = '[
          {
            "type": "Event",
            "public": true,
            "payload": {
          },
            "repo": {
              "id": 3,
              "name": "octocat/Hello-World",
              "url": "https://api.github.com/repos/octocat/Hello-World"
            },
            "actor": {
              "id": 1,
              "login": "octocat",
              "gravatar_id": "",
              "avatar_url": "https://github.com/images/error/octocat_happy.gif",
              "url": "https://api.github.com/users/octocat"
            },
            "org": {
              "id": 1,
              "login": "github",
              "gravatar_id": "",
              "url": "https://api.github.com/orgs/github",
              "avatar_url": "https://github.com/images/error/octocat_happy.gif"
            },
            "created_at": "2011-09-06T17:26:27Z",
            "id": "12345"
          }
        ]';

        $this->logger = $this->createMock(LoggerInterface::class);
    }

    public function testAppendResponseThrowsOnInvalidJSON(): void
    {
        $inputStream = $this->createMock(InputStream::class);

        $inputStream
            ->expects($this->exactly(2))
            ->method('read')
            ->willReturnOnConsecutiveCalls(new Success('[{"message"}]'), new Success(null))
        ;

        $message = new Payload($inputStream);

        $response = new Response('2', 200, 'OK', [], $message, new Request('foo'));

        $this->expectException(DecodingFailedException::class);
        $this->expectExceptionMessage('Failed to decode response body as JSON');

        Loop::run(function () use ($response) {
            yield (new Results(new Factory(), $this->logger))->appendResponse('Foo', $response);
        });
    }

    public function testAppendResponseUnknownEventExceptionDoesNotBubbleUp(): void
    {
        $inputStream = $this->createMock(InputStream::class);

        $inputStream
            ->expects($this->exactly(2))
            ->method('read')
            ->willReturnOnConsecutiveCalls(new Success($this->eventData), new Success(null))
        ;

        $message = new Payload($inputStream);

        $response = new Response('2', 200, 'OK', [], $message, new Request('foo'));

        $eventFactory = $this->createMock(Factory::class);

        $eventFactory
            ->expects($this->once())
            ->method('build')
            ->willThrowException(new UnknownEventException())
        ;

        Loop::run(function () use ($eventFactory, $response) {
            yield (new Results($eventFactory, $this->logger))
                ->appendResponse('ekinhbayar\GitAmp\Event\GitHub', $response);
        });
    }

    public function testHasEventsFalse(): void
    {
        $results = new Results(new Factory(), $this->logger);

        $this->assertFalse($results->hasEvents());
    }

    public function testHasEventsTrue(): void
    {
        $inputStream = $this->createMock(InputStream::class);

        $inputStream
            ->expects($this->exactly(2))
            ->method('read')
            ->willReturnOnConsecutiveCalls(new Success($this->eventData), new Success(null))
        ;

        $message = new Payload($inputStream);

        $response = new Response('2', 200, 'OK', [], $message, new Request('foo'));

        $eventFactory = $this->createMock(Factory::class);

        $eventFactory
            ->expects($this->once())
            ->method('build')
            ->will($this->returnValue($this->createMock(PullRequestEvent::class)))
        ;

        $results = new Results($eventFactory, $this->logger);

        Loop::run(function () use ($results, $response) {
            yield $results->appendResponse('ekinhbayar\GitAmp\Event\GitHub', $response);
        });

        $this->assertTrue($results->hasEvents());
    }

    public function testJsonEncodeWithoutEvents(): void
    {
        $results = new Results(new Factory(), $this->logger);

        $this->assertSame('[]', $results->jsonEncode());
    }

    public function testJsonEncodeWithEvents(): void
    {
        $inputStream = $this->createMock(InputStream::class);

        $inputStream
            ->expects($this->exactly(2))
            ->method('read')
            ->willReturnOnConsecutiveCalls(new Success($this->eventData), new Success(null))
        ;

        $message = new Payload($inputStream);

        $response = new Response('2', 200, 'OK', [], $message, new Request('foo'));

        $event = $this->createMock(PullRequestEvent::class);

        $event
            ->expects($this->once())
            ->method('getAsArray')
            ->will($this->returnValue([
                'foo' => 'bar',
            ]))
        ;

        $eventFactory = $this->createMock(Factory::class);

        $eventFactory
            ->expects($this->once())
            ->method('build')
            ->will($this->returnValue($event))
        ;

        $results = new Results($eventFactory, $this->logger);

        Loop::run(function () use ($results, $response) {
            yield $results->appendResponse('ekinhbayar\GitAmp\Event\GitHub', $response);
        });

        $this->assertSame('[{"foo":"bar"}]', $results->jsonEncode());
    }
}
