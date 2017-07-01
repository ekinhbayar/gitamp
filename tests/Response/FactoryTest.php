<?php declare(strict_types = 1);

namespace ekinhbayar\GitAmpTests\Response;

use Amp\Artax\Response;
use Amp\ByteStream\InputStream;
use Amp\ByteStream\Message;
use function Amp\Promise\wait;
use Amp\Success;
use ekinhbayar\GitAmp\Response\Factory;
use ekinhbayar\GitAmp\Response\Results;
use ekinhbayar\GitAmp\Event\Factory as EventFactory;
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

        $inputStream = $this->createMock(InputStream::class);
        $inputStream
            ->expects($this->at(0))
            ->method('read')
            ->willReturn(new Success($events))
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

        $logger = $this->createMock(LoggerInterface::class);

        $factory = new Factory(new EventFactory(), $logger);

        $results = wait($factory->build('ekinhbayar\GitAmp\Event\GitHub', $response));

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

        $inputStream = $this->createMock(InputStream::class);
        $inputStream
            ->expects($this->at(0))
            ->method('read')
            ->willReturn(new Success($events))
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

        $logger = $this->createMock(LoggerInterface::class);

        $results = (new Factory(new EventFactory(), $logger))->build('ekinhbayar\GitAmp\Event\GitHub', $response);

        $this->assertInstanceOf(Results::class, wait($results));
    }
}
