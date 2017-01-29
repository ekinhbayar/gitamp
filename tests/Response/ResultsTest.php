<?php declare(strict_types = 1);

namespace ekinhbayar\GitAmpTests\Response;

use Amp\Artax\Response;
use ekinhbayar\GitAmp\Events\Type\PullRequestEvent;
use ekinhbayar\GitAmp\Events\UnknownEventException;
use PHPUnit\Framework\TestCase;
use ekinhbayar\GitAmp\Events\Factory;
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
        $response = $this->createMock(Response::class);

        $response
            ->expects($this->once())
            ->method('getBody')
            ->will($this->returnValue('[{"message"}]'))
        ;

        $this->expectException(DecodingFailedException::class);
        $this->expectExceptionMessage('Failed to decode response body as JSON');

        (new Results(new Factory(), $this->logger))->appendResponse($response);
    }

    public function testAppendResponseUnknownEventExceptionDoesNotBubbleUp()
    {
        $response = $this->createMock(Response::class);

        $response
            ->expects($this->once())
            ->method('getBody')
            ->will($this->returnValue($this->eventData))
        ;

        $eventFactory = $this->createMock(Factory::class);

        $eventFactory
            ->expects($this->once())
            ->method('build')
            ->willThrowException(new UnknownEventException())
        ;

        (new Results($eventFactory, $this->logger))->appendResponse($response);
    }

    public function testHasEventsFalse()
    {
        $results = new Results(new Factory(), $this->logger);

        $this->assertFalse($results->hasEvents());
    }

    public function testHasEventsTrue()
    {
        $response = $this->createMock(Response::class);

        $response
            ->expects($this->once())
            ->method('getBody')
            ->will($this->returnValue($this->eventData))
        ;

        $eventFactory = $this->createMock(Factory::class);

        $eventFactory
            ->expects($this->once())
            ->method('build')
            ->will($this->returnValue($this->createMock(PullRequestEvent::class)))
        ;

        $results = new Results($eventFactory, $this->logger);

        $results->appendResponse($response);

        $this->assertTrue($results->hasEvents());
    }

    public function testJsonEncodeWithoutEvents()
    {
        $results = new Results(new Factory(), $this->logger);

        $this->assertSame('[]', $results->jsonEncode());
    }

    public function testJsonEncodeWithEvents()
    {
        $response = $this->createMock(Response::class);

        $response
            ->expects($this->once())
            ->method('getBody')
            ->will($this->returnValue($this->eventData))
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

        $results->appendResponse($response);

        $this->assertSame('[{"foo":"bar"}]', $results->jsonEncode());
    }
}
