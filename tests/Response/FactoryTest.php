<?php declare(strict_types = 1);

namespace ekinhbayar\GitAmpTests\Response;

use Amp\Artax\Response;
use ekinhbayar\GitAmp\Response\Factory;
use ekinhbayar\GitAmp\Response\Results;
use ekinhbayar\GitAmp\Events\Factory as EventFactory;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class FactoryTest extends TestCase
{
    public function testBuildResultsUnknownEventDoesNotBubbleUp()
    {
        $events = json_encode([
            [
                'id'        => 1,
                'type'      => 'InvalidEvent',
                'action'    => 'created',
                'repoName'  => 'test/repo',
                'actorName' => 'PeeHaa',
                'eventUrl'  => 'https://github.com/test/repo',
                'message'   => 'The description',
            ],
        ]);

        $response = $this->createMock(Response::class);

        $response
            ->expects($this->once())
            ->method('getBody')
            ->will($this->returnValue($events))
        ;

        $logger = $this->createMock(LoggerInterface::class);

        $results = (new Factory(new EventFactory(), $logger))->build($response);

        $this->assertInstanceOf(Results::class, $results);
    }

    public function testBuildReturnsResult()
    {
        $events = json_encode([
            [
                'id'        => 1,
                'type'      => 'CreateEvent',
                'action'    => 'created',
                'repo'      => ['name' => 'test/repo'],
                'actor'     => ['login' => 'PeeHaa'],
                'eventUrl'  => 'https://github.com/test/repo',
                'message'   => 'The description',
            ],
        ]);

        $response = $this->createMock(Response::class);

        $response
            ->expects($this->once())
            ->method('getBody')
            ->will($this->returnValue($events))
        ;

        $logger = $this->createMock(LoggerInterface::class);

        $results = (new Factory(new EventFactory(), $logger))->build($response);

        $this->assertInstanceOf(Results::class, $results);
    }
}
