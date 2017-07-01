<?php declare(strict_types = 1);

namespace ekinhbayar\GitAmpTests\Response;

use Amp\Artax\Response;
use Amp\ByteStream\InputStream;
use Amp\ByteStream\Message;
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
    private $eventData;

    private $logger;

    public function setUp()
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

    public function testAppendResponseThrowsOnInvalidJSON()
    {
        $inputStream = $this->createMock(InputStream::class);

        $inputStream
            ->expects($this->at(0))
            ->method('read')
            ->willReturn(new Success('[{"message"}]'))
        ;

        $inputStream
            ->expects($this->at(1))
            ->method('read')
            ->willReturn(new Success(null))
        ;

        $message = new Message($inputStream);

        $response = $this->createMock(Response::class);

        $response
            ->expects($this->once())
            ->method('getBody')
            ->will($this->returnValue($message))
        ;

        $this->expectException(DecodingFailedException::class);
        $this->expectExceptionMessage('Failed to decode response body as JSON');

        Loop::run(function() use ($response) {
            yield from (new Results(new Factory(), $this->logger))->appendResponse('Foo', $response);
        });
    }

    public function testAppendResponseUnknownEventExceptionDoesNotBubbleUp()
    {
        $inputStream = $this->createMock(InputStream::class);

        $inputStream
            ->expects($this->at(0))
            ->method('read')
            ->willReturn(new Success($this->eventData))
        ;

        $inputStream
            ->expects($this->at(1))
            ->method('read')
            ->willReturn(new Success(null))
        ;

        $message = new Message($inputStream);

        $response = $this->createMock(Response::class);

        $response
            ->expects($this->once())
            ->method('getBody')
            ->will($this->returnValue($message))
        ;

        $eventFactory = $this->createMock(Factory::class);

        $eventFactory
            ->expects($this->once())
            ->method('build')
            ->willThrowException(new UnknownEventException())
        ;

        Loop::run(function() use ($eventFactory, $response) {
            yield from (new Results($eventFactory, $this->logger))
                ->appendResponse('ekinhbayar\GitAmp\Event\GitHub', $response);
        });
    }

    public function testHasEventsFalse()
    {
        $results = new Results(new Factory(), $this->logger);

        $this->assertFalse($results->hasEvents());
    }

    public function testHasEventsTrue()
    {
        $inputStream = $this->createMock(InputStream::class);

        $inputStream
            ->expects($this->at(0))
            ->method('read')
            ->willReturn(new Success($this->eventData))
        ;

        $inputStream
            ->expects($this->at(1))
            ->method('read')
            ->willReturn(new Success(null))
        ;

        $message = new Message($inputStream);

        $response = $this->createMock(Response::class);

        $response
            ->expects($this->once())
            ->method('getBody')
            ->will($this->returnValue($message))
        ;

        $eventFactory = $this->createMock(Factory::class);

        $eventFactory
            ->expects($this->once())
            ->method('build')
            ->will($this->returnValue($this->createMock(PullRequestEvent::class)))
        ;

        $results = new Results($eventFactory, $this->logger);

        Loop::run(function() use ($results, $response) {
            yield from $results->appendResponse('ekinhbayar\GitAmp\Event\GitHub', $response);
        });

        $this->assertTrue($results->hasEvents());
    }

    public function testJsonEncodeWithoutEvents()
    {
        $results = new Results(new Factory(), $this->logger);

        $this->assertSame('[]', $results->jsonEncode());
    }

    public function testJsonEncodeWithEvents()
    {
        $inputStream = $this->createMock(InputStream::class);

        $inputStream
            ->expects($this->at(0))
            ->method('read')
            ->willReturn(new Success($this->eventData))
        ;

        $inputStream
            ->expects($this->at(1))
            ->method('read')
            ->willReturn(new Success(null))
        ;

        $message = new Message($inputStream);

        $response = $this->createMock(Response::class);

        $response
            ->expects($this->once())
            ->method('getBody')
            ->will($this->returnValue($message))
        ;

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

        Loop::run(function() use ($results, $response) {
            yield from $results->appendResponse('ekinhbayar\GitAmp\Event\GitHub', $response);
        });

        $this->assertSame('[{"foo":"bar"}]', $results->jsonEncode());
    }
}
